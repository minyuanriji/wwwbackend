<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 微信相关公共类
 * Author: zal
 * Date: 2020-07-30
 * Time: 14:18
 */

namespace app\forms\common;

use app\component\caches\UserCache;
use app\core\ApiCode;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\User;
use app\models\UserInfo;
use jianyan\easywechat\Wechat;

class WechatCommon extends BaseModel
{
    public $iv;
    public $encryptedData;

    /**
     * 授权手机号
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\DecryptException
     */
    public function getAuthorizedMobilePhone(){
        try{
            $userCache = new UserCache();
            $key = "_authorized_session_key_".\Yii::$app->user->id;
            $sessionKey = $userCache->getValue($key);
            if(empty($sessionKey)){
                $userInfo = UserInfo::find()->where(["mall_id" => \Yii::$app->mall->id,"user_id" => \Yii::$app->user->id,
                                                     "platform" => \Yii::$app->appPlatform,"is_delete" => UserInfo::IS_DELETE_NO])
                                            ->asArray()->one();
                if(empty($userInfo["platform_data"])){
                    return $this->returnApiResultData(ApiCode::CODE_FAIL,"参数有误");
                }
                $platformData = json_decode($userInfo["platform_data"],true);
                $sessionKey = isset($platformData["session_key"]) ? $platformData["session_key"] : "";
            }
            /** @var Wechat $wechatModel */
            $wechatModel = \Yii::$app->wechat;
            $data = $wechatModel->miniProgram->encryptor->decryptData($sessionKey,$this->iv,$this->encryptedData);
            \Yii::warning("getAuthorizedMobilePhone data=".json_encode($data));
            $phoneNumber = $data["phoneNumber"];
            //用户表没有mobile值，则更新授权后的手机号
            $userModel = User::findOne(\Yii::$app->user->id);
            if(empty($userModel->mobile)){
                $userModel->mobile = $phoneNumber;
                $result = $userModel->save();
                if($result === false){
                    return false;
                }
            }
            return ["mobile" => $phoneNumber];
        }catch (\Exception $ex){
            \Yii::error(" WechatCommon getAuthorizedMobilePhone ".CommonLogic::getExceptionMessage($ex));
            return false;
        }
    }
}