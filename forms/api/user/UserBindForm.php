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
use app\forms\api\identity\SmsForm;
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
    public function bind()
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
            if(empty($userInfo)){
                throw new \Exception("数据不存在");
            }
            \Yii::warning("userBindForm start mobile = ".$this->mobile);
            //手机号是否已绑定
            $userResult = UserLogic::checkUserMobileIsExist($this->mobile);
            \Yii::warning("userBindForm platFrom=".\Yii::$app->appPlatform." userResult = ".var_export($userResult,true));
            //已经绑定的情况
            if($userResult){
                //当前登录用户是不是已经授权了当前平台
                /** @var UserInfo $currentUserInfo */
                $currentUserInfo = UserInfo::getOneUserInfo(["user_id" => \Yii::$app->user->id,"platform" => \Yii::$app->appPlatform,'is_delete' => 0]);
                \Yii::warning("userBindForm currentUserInfo = ".var_export($currentUserInfo,true));
                \Yii::$app->user->logout();
                \Yii::$app->user->login($userResult);
                //绑定过的用户是不是已经授权了当前平台
                $bindUserInfo = UserInfo::getOneUserInfo(["user_id" => $userResult->id,"platform" => \Yii::$app->appPlatform,'is_delete' => 0]);
                \Yii::warning("userBindForm bindUserInfo = ".var_export($bindUserInfo,true));
                //如果绑定过的用户也已经授权了当前平台，则不做操作，如果没有授权，则将当前登录用户的授权信息更新到绑定过的账户上
                if(!empty($currentUserInfo) && empty($bindUserInfo)){
                    \Yii::warning("userBindForm auth save ");
                    //当前授权的用户id
                    $currentUserId = $currentUserInfo->user_id;
                    $currentUserInfo->user_id = $userResult->id;
                    $result = $currentUserInfo->save();
                    if($result !== false){
                        //删除了授权的用户，因为之前已经绑定了对应手机的用户
                        $users = User::findOne($currentUserId);
                        if(!empty($users)){
                            $users->is_delete = 1;
                            $res = $users->save();
                            \Yii::warning("userBindForm res = ".$res.";error:".var_export($users->getErrors(),true));
                        }
                    }
                }else if(!empty($bindUserInfo)){
                    throw new \Exception("手机号已经被其他用户绑定过了");
                }else if(empty($bindUserInfo)){
                    //当前平台没有该用户信息，就新增一条
                    $userResult = UserLogic::userRegister($userInfo,$userResult);
                }
            }else{
                //没有绑定手机号
                /** @var UserInfo $currentUserInfo */
                $currentUserInfo = UserInfo::getOneUserInfo(["user_id" => \Yii::$app->user->id,"platform" => \Yii::$app->appPlatform,'is_delete' => 0]);
                if(empty($currentUserInfo)){
                    $userInfo["mobile"] = $this->mobile;
                    $userResult = UserLogic::userRegister($userInfo);
                    if($userResult === false){
                        return $this->returnApiResultData(ApiCode::CODE_FAIL,'绑定失败');
                    }
                }else{
                    $userResult = User::findOne(\Yii::$app->user->id);
                    $userResult->mobile = trim($this->mobile);
                    $res = $userResult->save();
                    if($res === false){
                        throw new \Exception("绑定失败！");
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
