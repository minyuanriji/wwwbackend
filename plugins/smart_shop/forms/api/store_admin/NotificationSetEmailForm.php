<?php

namespace app\plugins\smart_shop\forms\api\store_admin;

use app\core\ApiCode;
use app\helpers\CommonHelper;
use app\models\BaseModel;
use app\plugins\smart_shop\models\Notifications;

class NotificationSetEmailForm extends BaseModel{

    public $merchant_id;
    public $store_id;
    public $email;

    public function rules(){
        return [
            [['merchant_id', 'store_id', 'email'], 'required'],
            [['email'], 'trim']
        ];
    }

    public function set(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            if(!CommonHelper::isEmail($this->email)){
                throw new \Exception("邮箱格式不正确");
            }

            $uniqueData = [
                "mall_id"     => \Yii::$app->mall->id,
                "ss_mch_id"   => $this->merchant_id,
                "ss_store_id" => $this->store_id,
                "type"        => "email"
            ];
            $notification = Notifications::findOne($uniqueData);
            if(!$notification){
                $notification = new Notifications(array_merge($uniqueData, [
                    "created_at" => time()
                ]));
            }
            $notification->updated_at = time();
            $notification->enable     = 1;
            $notification->data_json  = json_encode(["email" => $this->email]);
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