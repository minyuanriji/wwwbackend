<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * api注册类
 * Author: zal
 * Date: 2020-04-27
 * Time: 15:16
 */

namespace app\forms\api\identity;

use app\component\jobs\ParentChangeJob;
use app\events\TagEvent;
use app\handlers\TagHandler;
use app\helpers\ArrayHelper;
use app\models\RelationSetting;
use app\helpers\sms\Sms;
use app\logic\CommonLogic;
use app\logic\RelationLogic;
use app\models\BaseModel;
use app\core\ApiCode;
use app\models\User;
use app\models\UserInfo;
use app\validators\PhoneNumberValidator;
use function EasyWeChat\Kernel\Support\get_client_ip;

class RegisterForm extends BaseModel
{
    public $parent_mobile;
    public $mobile;
    public $password;
    public $confirm_password;
    public $recommend_id = 0;
    public $mall_id = 0;
    public $captcha;

    public function rules()
    {
        return [
            [['parent_mobile','mobile', 'captcha'], 'required'],
            [['recommend_id','mall_id'],"integer"],
            [['mobile','parent_mobile'], PhoneNumberValidator::className()],
        ];
    }

    public function attributeLabels()
    {
        return [
            'mobile' => '手机号',
            'recommend_id' => '推荐人id',
            'parent_mobile' => '推荐人手机号码',
            'password' => '密码',
            'mall_id' => '商城ID',
            'captcha' => '验证码',
        ];
    }

    /**
     * 上级信息
     * @Author: zal
     * @Date: 2020-04-27
     * @Time: 10:33
     * @return array
     * @throws \Exception
     */
    public function parentInfo(){
         if (!$this->recommend_id) {
             return $this->returnApiResultData();
        }
        
        try {
            /** @var User $user */
            $user = User::getOneUser(['or',['=', 'id', $this->recommend_id],['=', 'mobile', $this->recommend_id]]);
            if (empty($user)) {
                throw new \Exception('邀请人不存在');
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"获取成功",['avatar'=>$user['avatar_url'],'nicknamem'=>$user['nickname']?$user['nickname']:['mobile'],'pid'=>$user['id']]);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,$e->getMessage());
        }
    }

    /**
     * 绑定上级
     * @Author: vita
     * @Date: 2020-12-27
     * @Time: 10:33
     * @return array
     * @throws \Exception
     */
    public function bindParent()
    {
        if(!$this->parent_mobile){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,'请输入推荐人手机号');
        }
        $trans = \Yii::$app->db->beginTransaction();
        try {
            $user_id = \Yii::$app->user->identity->id;
            
            $relation = RelationSetting::findOne(['mall_id' => \Yii::$app->mall->id, 'use_relation' => 1, 'is_delete' => 0]);
            if (!$relation) {
                throw new \Exception('未启用关系链');
            }

            $user = User::getOneData([
                'id' => $user_id,
                'mall_id' => \Yii::$app->mall->id
            ]);
            $beforeParentId = $user->parent_id;
            if($user->parent_id){
                return $this->returnApiResultData(ApiCode::CODE_FAIL,'已经存在推荐人');
            }

            $recommendUsers = User::getOneUser(['=', 'mobile', $this->parent_mobile]);
            if (!$recommendUsers) {
                throw new \Exception('推荐人手机号不存在');
            }
            if (!$recommendUsers->is_inviter) {
                throw new \Exception('绑定的手机号没有推广资格');
            }
            if ($recommendUsers->id == $user->id) {
                throw new \Exception('自己不能绑定自己');
            }
            $second_parent_id = $recommendUsers["parent_id"];
            $third_parent_id = $recommendUsers["second_parent_id"];
            
            $user->id = $user_id;
            $user->parent_id = $recommendUsers['id'];
            $user->second_parent_id = $second_parent_id;
            $user->third_parent_id = $third_parent_id;
            if (!$user->save()) {
                $messages = $this->responseErrorInfo($user);
                throw new \Exception($messages["msg"]);
            }else{
                $user->bindParent($beforeParentId);
            }
            $trans->commit();
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'绑定成功');
        } catch (\Exception $e) {
            $trans->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }

    /**
     * 注册
     * @Author: zal
     * @Date: 2020-04-27
     * @Time: 10:33
     * @return array
     * @throws \Exception
     */
    public function register()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        $trans = \Yii::$app->db->beginTransaction();
        try {
//            if($this->password !== $this->confirm_password){
//                return $this->returnApiResultData(ApiCode::CODE_FAIL,'两次密码不一致');
//            }
            // 粗糙版 上级绑定
            // $existParentUser = User::getOneUser(['=', 'mobile', $this->parent_mobile]);
            // if(empty($existParentUser)){
            //     return $this->returnApiResultData(ApiCode::CODE_FAIL,'没有找到该邀请人手机号');
            // }
            // $this->recommend_id = $existParentUser['id'];

            $existUser = User::getOneUser(['or',['=', 'username', $this->mobile],['=', 'mobile', $this->mobile]]);
            if(!empty($existUser)){
                return $this->returnApiResultData(ApiCode::CODE_FAIL,'手机号已被注册');
            }
            $third_parent_id = $second_parent_id = 0;
            if(!empty($this->recommend_id)){
                $recommendUsers = User::findOne($this->recommend_id);
                if(empty($recommendUsers)){
                    return $this->returnApiResultData(ApiCode::CODE_FAIL,'推荐人不存在');
                }
                $second_parent_id = $recommendUsers["parent_id"];
                $third_parent_id = $recommendUsers["second_parent_id"];
            }
            $smsForm = new SmsForm();
            $smsForm->captcha = $this->captcha;
            $smsForm->mobile = $this->mobile;
            if(!$smsForm->checkCode()){
                return $this->returnApiResultData(ApiCode::CODE_FAIL,'验证码不正确');
            }
            $user = new User();
            $user->username = $this->mobile;
            $user->mobile = $this->mobile;
            $user->mall_id = $this->mall_id;
            $user->access_token = \Yii::$app->security->generateRandomString();
            $user->auth_key = \Yii::$app->security->generateRandomString();
            $user->nickname = "";
            $this->password = 'myrj2021';//"jx888888";
            $user->password = \Yii::$app->getSecurity()->generatePasswordHash($this->password);
            $user->avatar_url = "";
            $user->last_login_at = time();
            $user->login_ip = get_client_ip();
            $user->parent_id = $this->recommend_id;
            $user->second_parent_id = $second_parent_id;
            $user->third_parent_id = $third_parent_id;
            if (!$user->save()) {
                $messages = $this->responseErrorInfo($user);
                throw new \Exception($messages["msg"]);
            }
            $userInfoModel = new UserInfo();
            $userInfoModel->mall_id = $this->mall_id;
            $userInfoModel->mch_id = 0;
            $userInfoModel->user_id = $user->id;
            $userInfoModel->unionid = "";
            $userInfoModel->openid = "";
            $userInfoModel->platform_data = "";
            $userInfoModel->platform = \Yii::$app->appPlatform;
            if (!$userInfoModel->save()) {
                $messages = $this->responseErrorInfo($userInfoModel);
                throw new \Exception($messages["msg"]);
            }
            Sms::updateCodeStatus($this->mobile, $this->captcha);
            \Yii::$app->user->login($user);
            $trans->commit();
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'注册成功',['access_token' => $user->access_token]);
        } catch (\Exception $e) {
            $trans->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }
}
