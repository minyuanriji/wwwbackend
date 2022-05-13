<?php

namespace app\plugins\smart_shop\forms\api\store_admin;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\smart_shop\helpers\NotificationHelper;

class NotificationDetailForm extends BaseModel{

    public $merchant_id;
    public $store_id;

    public function rules(){
        return [
            [['merchant_id', 'store_id'], 'required']
        ];
    }

    public function get(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $notifications = [];
            $notifications[] = NotificationHelper::getWechatTemplate(\Yii::$app->mall->id, $this->merchant_id, $this->store_id);
            $notifications[] = NotificationHelper::getMobile(\Yii::$app->mall->id, $this->merchant_id, $this->store_id);
            $notifications[] = NotificationHelper::getEmail(\Yii::$app->mall->id, $this->merchant_id, $this->store_id);

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, [
                "notification" => $notifications
            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }

    }
}