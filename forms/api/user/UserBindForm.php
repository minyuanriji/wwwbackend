<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 用户绑定
 * Author: zal
 * Date: 2020-05-23
 * Time: 11:50
 */

namespace app\forms\api\user;

use app\core\ApiCode;
use app\forms\api\identity\{SmsForm,RegisterForm};
use app\helpers\sms\Sms;
use app\logic\CommonLogic;
use app\logic\UserLogic;
use app\models\BaseModel;
use app\models\User;
use app\models\UserInfo;
use yii\base\Exception;

class UserBindForm extends BaseModel
{
    public $key;
    public $mobile;
    public $captcha;

    public function rules()
    {
        return [
            [['key',"mobile",'captcha'],'string']
        ];
    }

    /**
     * 绑定
     * @Author: zal
     * @Date: 2020-05-07
     * @Time: 14:33
     * @return array
     */
    public function bind($parent_id,$stands_mall_id)
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        //手机验证码验证
        $smsForm = new SmsForm();
        $smsForm->mobile = $this->mobile;
        $smsForm->captcha = $this->captcha;
        $result = $smsForm->checkCode();
        if (!$result) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,'验证码不正确');
        }
        try {
            $userInfo = \Yii::$app->cache->get($this->key);

            /*$userInfo = [
                "openId" => "ozvug4lfH__6SIvHWgCwaoKCwDiM",
                "nickName" => "微信用户",
                "gender" => 0,
                "language" => "",
                "city" => "",
                "province" => "",
                "country" => "",
                "avatarUrl" => "https://thirdwx.qlogo.cn/mmopen/vi_32/POgEwh4mIHO4nibH0KlMECNjjGxQUq24ZEaGT4poC6icRiccVGKSyXwibcPq4BWmiaIGuG1icwxaQX6grC9VemZoJ8rg/132",
                "unionId" => "o2jF35uJT9pCMpQLkyWb6LYZM-Qk",
                "watermark" => [
                    "timestamp" => "1628660972",
                    "appid" => "wx3ab6add3406998a1"
                ],
                "openid" => "ozvug4lfH__6SIvHWgCwaoKCwDiM",
                "nickname" => "微信用户",
                "headimgurl" => "https://thirdwx.qlogo.cn/mmopen/vi_32/POgEwh4mIHO4nibH0KlMECNjjGxQUq24ZEaGT4poC6icRiccVGKSyXwibcPq4BWmiaIGuG1icwxaQX6grC9VemZoJ8rg/132",
                "session_key" => "o3O+o1TIbGxax7IZxqtucw==",
                "unionid" => "o2jF35uJT9pCMpQLkyWb6LYZM-Qk"
            ];*/


            if(empty($userInfo)){
                return [
                    'code' => ApiCode::CODE_NOT_LOGIN,
                    'msg' => '授权信息已过期，请重新登陆！',
                ];
            }
            \Yii::warning("userBindForm start mobile = ".$this->mobile);
            //手机号是否已绑定
            $userResult = UserLogic::checkUserMobileIsExist($this->mobile);
            \Yii::warning("userBindForm platFrom=".\Yii::$app->appPlatform." userResult = ".var_export($userResult,true));

            if($userResult){ //已存在绑定用户

                \Yii::$app->user->logout();
                \Yii::$app->user->login($userResult);

                //判断授权信息
                $uniqueData = [
                    "mall_id"  => $stands_mall_id,
                    "platform" => \Yii::$app->appPlatform
                ];
                if(!empty($userInfo['openid'])){
                    $uniqueData['openid'] = $userInfo['openid'];
                }else{
                    $uniqueData['user_id'] = $userResult->id;
                }
                $userInfoModel = UserInfo::findOne($uniqueData);
                if(!$userInfoModel){ //没有授权信息就生成一条
                    $userInfoModel = new UserInfo($uniqueData);
                }
                $userInfoModel->mch_id        = 0;
                $userInfoModel->user_id       = $userResult->id;
                $userInfoModel->openid        = isset($userInfo['openid']) ? $userInfo['openid'] : "";
                $userInfoModel->unionid       = isset($userInfo["unionid"]) ? $userInfo["unionid"] : "";
                $userInfoModel->platform_data = isset($userInfo["platform_data"]) ? $userInfo["platform_data"] : "";
                if(!$userInfoModel->save()) {
                    throw new Exception("用户授权信息新增失败");
                }

                $userResult->access_token = \Yii::$app->security->generateRandomString();
                if (!$userResult->save()) {
                    \Yii::error("UpdateUserAccessToken ".var_export($userResult->getErrors(),true));
                    throw new Exception("更新用户access_token失败");
                }
            }else{
                //没有绑定手机号
                /** @var UserInfo $currentUserInfo */
                $currentUserInfo = UserInfo::getOneUserInfo(["user_id" => \Yii::$app->user->id, "mall_id" => $stands_mall_id,"platform" => \Yii::$app->appPlatform,'is_delete' => 0]);
                if(empty($currentUserInfo)){
                    $userInfo["mobile"] = $this->mobile;
                    $userResult = UserLogic::userRegister($userInfo,[],$parent_id,$stands_mall_id);
                    if($userResult === false){
                        return $this->returnApiResultData(ApiCode::CODE_FAIL,UserLogic::$error);
                    }
                }else{
                    $userResult = User::findOne(\Yii::$app->user->id);
                    $userResult->mobile = trim($this->mobile);
                    $res = $userResult->save();
                    if($res === false){
                        throw new \Exception($this->responseErrorMsg($userResult));
                    }
                }
            }
            \Yii::warning("userBindForm end ");
            Sms::updateCodeStatus($this->mobile, $this->captcha);
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"绑定成功",['mobile' => $this->mobile,'access_token' => $userResult->access_token]);
        } catch (\Exception $e) {
            $message = CommonLogic::getExceptionMessage($e);
            \Yii::error("userBindForm error = ".$message);
            return $this->returnApiResultData(ApiCode::CODE_FAIL,$message);
        }
    }
}


