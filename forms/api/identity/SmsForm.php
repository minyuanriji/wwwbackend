<?php
namespace app\forms\api\identity;

use app\core\ApiCode;
use app\helpers\sms\Sms;
use app\logic\AppConfigLogic;
use app\logic\CommonLogic;
use app\logic\OptionLogic;
use app\logic\UserLogic;
use app\models\BaseModel;
use app\models\Option;
use app\models\User;
use app\models\UserInfo;
use app\validators\PhoneNumberValidator;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use yii\base\Exception;

class SmsForm extends BaseModel
{
    public $mobile;
    public $captcha;
    public $key;
    public $type;

    public function rules()
    {
        return [
            [['mobile'], 'required'],
            [['mobile'], PhoneNumberValidator::className()],
            [['captcha',"key"], 'string'],
            [['type'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'mobile' => '手机号',
            'captcha' => '验证码',
            'key' => 'key',
        ];
    }

    /**
     * 获取手机验证码
     * @Author: zal
     * @Date: 2020-04-27
     * @Time: 10:33
     * @return array
     * @throws \Exception
     */
    public function getPhoneCode()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        try {
            $sms = new Sms();
            $res = OptionLogic::get(
                Option::NAME_SMS,
                \Yii::$app->mall->id,
                Option::GROUP_ADMIN
            );
            if(!$res || $res['status'] == 0) {
                throw new \Exception('验证码功能未开启');
            }
            $sms->sendCaptcha($this->mobile, $this->type);
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"验证码获取成功");
        } catch (\Exception $exception) {
            if($exception instanceof NoGatewayAvailableException) {
                //$exception = $exception->results['aliyun']['exception'];
//                $msg = '验证码配置错误';
                $msg = '短信发送已达上限';
            } else {
                $msg = $exception->getMessage();
            }
//            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($exception,$msg));
            return $this->returnApiResultData(ApiCode::CODE_FAIL,$msg);
        }
    }

    /**
     * 绑定手机号（下单的时候绑定手机号）
     * @return array
     * @throws \Exception
     */
    public function bindUserMobile()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }

        $result = $this->checkCode();
        if (!$result) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,'验证码不正确');
        }
        $existMobileUser = User::find()->where(["mobile" => $this->mobile])->one();
        if($existMobileUser){
            if(!\Yii::$app->user->isGuest){
                $identity = \Yii::$app->user->getIdentity();
                if($existMobileUser->id != $identity->id){
                    if(!empty($identity->mobile)){
                        return $this->returnApiResultData(ApiCode::CODE_FAIL,"已经绑定过手机号了,无需绑定");
                    }
                    UserInfo::updateAll(["user_id" => $existMobileUser->id], ["user_id" => $identity->id]);
                    $currentAccessToken = $identity->access_token;
                    $identity->access_token = \Yii::$app->security->generateRandomString();
                    $identity->save();
                    $existMobileUser->access_token = $currentAccessToken;
                    $existMobileUser->save();
                }
                return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"绑定成功",['mobile' => $this->mobile]);
            }
            return $this->returnApiResultData(ApiCode::CODE_FAIL,'手机号”'.$this->mobile.'“已被其它账号绑定');
        }

        $phoneConfig = AppConfigLogic::getPhoneConfig();
        $user = User::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => \Yii::$app->user->id,
            'is_delete' => 0
        ]);
        if($phoneConfig["all_network_enable"] == 1 && !empty($user->mobile)){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,'已经绑定过手机号了,无需绑定');
        }

        if(empty($phoneConfig["all_network_enable"]) && $phoneConfig["bind_phone_enable"] == 1 && (!empty($user->mobile))){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,'已经绑定过手机号了,无需绑定');
        }

        //检测手机号是否已经绑定过账户，如果是，返回该绑定过的账户，并退出当前登录，重新登录新账户
        //$userResult = UserLogic::checkUserMobileIsExist($this->mobile);
        $user->mobile = $this->mobile;
        if ($user->save()) {
            Sms::updateCodeStatus($this->mobile, $this->captcha);
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"绑定成功",['mobile' => $this->mobile]);
        } else {
            return $this->returnApiResultData();
        }
    }

    /**
     * 绑定（授权之后绑定手机号） 废弃
     * @return array
     * @throws \Exception
     */
    public function bind()
    {
        return ;
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        $result = $this->checkCode();
        if (!$result) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,'验证码不正确');
        }
        try {
            $userInfo = \Yii::$app->cache->get($this->key);
            if(empty($userInfo)){
                throw new \Exception("数据不存在");
            }
            //是否授权过，再一次做授权验证，防止直接跳过授权接口，直接请求绑定手机接口
            $isAuthorizedUser = UserLogic::checkIsAuthorized($userInfo);
            if(!empty($isAuthorizedUser)){
                return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"操作成功",["access_token" => $isAuthorizedUser->access_token]);
            }
            //手机号是否已绑定
            $userResult = UserLogic::checkUserMobileIsExist($this->mobile);
            if($userResult){
                \Yii::$app->user->login($userResult);
                //绑定过的用户是不是已经授权了当前平台
                $bindUserInfo = UserInfo::getOneUserInfo(["user_id" => $userResult->id,"platform" => \Yii::$app->appPlatform]);
                //如果没有当前平台授权信息，则新增
                if(empty($bindUserInfo)){
                    $userInfoModel = new UserInfo();
                    $userInfoModel->mall_id = \Yii::$app->mall->id;
                    $userInfoModel->mch_id = 0;
                    $userInfoModel->user_id = $userResult->id;
                    $userInfoModel->unionid = isset($userInfo["unionid"]) ? $userInfo["unionid"] : "";
                    $userInfoModel->openid = isset($userInfo["openid"]) ? $userInfo["openid"] : "";
                    $userInfoModel->platform_data = json_encode($userInfo);
                    $userInfoModel->platform = \Yii::$app->appPlatform;
                    if ($userInfoModel->save() === false) {
                        \Yii::error("bind userInfo add ".var_export($userInfoModel->getErrors(),true));
                        throw new Exception("用户信息新增失败");
                    }
                }
                //如果绑定过的用户也已经授权了当前平台，则不做操作，如果没有授权，则将当前登录用户的授权信息更新到绑定过的账户上
                Sms::updateCodeStatus($this->mobile, $this->captcha);
                return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"绑定成功",['mobile' => $this->mobile,'access_token' => $userResult->access_token]);
            }
            /** @var User $userResult */
            $userInfo["mobile"] = $this->mobile;
            $userResult = UserLogic::userRegister($userInfo);
            if($userResult === false){
                return $this->returnApiResultData(ApiCode::CODE_FAIL,'授权失败');
            }
            \Yii::$app->user->login($userResult);
            $returnData = ['access_token' => $userResult->access_token];
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"操作成功",$returnData);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }

    /**
     * 检测手机验证码
     * @Author: zal
     * @Date: 2020-04-27
     * @Time: 10:33
     * @return bool
     * @throws \Exception
     */
    public function checkCode(){
        //测试验证码1
//         return true;
        $result = Sms::checkValidateCode($this->mobile, $this->captcha);
        if (!$result) {
            return false;
        }
        return true;
    }
}
