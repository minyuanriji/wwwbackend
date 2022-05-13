<?php

namespace app\plugins\smart_shop\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\UserInfo;
use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\models\Notifications;

class NotificationSetWechatTemplateForm extends BaseModel{

    public $ss_mch_id;
    public $ss_store_id;

    public function rules(){
        return [
            [['ss_store_id'], 'required'],
            [['ss_store_id', 'ss_mch_id'], 'integer']
        ];
    }

    public function set(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $smartShop = new SmartShop();
            $storeDetail = $smartShop->getStoreDetail($this->ss_store_id);
            if(!$storeDetail){
                throw new \Exception("无法获取到门店信息");
            }

            $userInfo = UserInfo::findOne([
                "user_id" => \Yii::$app->user->id,
                "platform" => "wechat"
            ]);
            if(!$userInfo){
                throw new \Exception("无法获取到授权信息");
            }

            $uniqueData = [
                "mall_id"     => \Yii::$app->mall->id,
                "ss_mch_id"   => $storeDetail['merchant_id'],
                "ss_store_id" => $this->ss_store_id,
                "type"        => "wechat_template"
            ];
            $notification = Notifications::findOne($uniqueData);
            if(!$notification){
                $notification = new Notifications(array_merge($uniqueData, [
                    "created_at" => time()
                ]));
            }
            $notification->updated_at = time();
            $notification->enable     = 1;
            $notification->data_json  = json_encode(["openid" => $userInfo->openid]);
            if(!$notification->save()){
                throw new \Exception($this->responseErrorMsg($notification));
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, [

            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}