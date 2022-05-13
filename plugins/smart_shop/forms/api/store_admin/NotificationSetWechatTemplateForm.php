<?php

namespace app\plugins\smart_shop\forms\api\store_admin;

use app\core\ApiCode;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\User;

class NotificationSetWechatTemplateForm extends BaseModel{

    public $merchant_id;
    public $store_id;

    public function rules(){
        return [
            [['merchant_id', 'store_id'], 'required']
        ];
    }

    public function set(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $dir = 'smartshop/notification/set-wechat-template/' . $this->store_id . "/" . date("ymdh") . '.jpg';

            $path = "/h5/#/smartshop/notification/setWechatTemplate?mall_id=".\Yii::$app->mall->id . "&ss_mch_id=".$this->merchant_id."&ss_store_id=" . $this->store_id;
            $file = CommonLogic::createQrcode(null, $this, $path, $dir);

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, [
                "qrcode" => base64_encode(file_get_contents($file))
            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

}