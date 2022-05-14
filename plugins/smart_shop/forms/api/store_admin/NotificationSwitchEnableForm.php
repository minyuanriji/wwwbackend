<?php

namespace app\plugins\smart_shop\forms\api\store_admin;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\smart_shop\models\Notifications;

class NotificationSwitchEnableForm  extends BaseModel{

    public $id;
    public $merchant_id;
    public $store_id;
    public $enable;

    public function rules(){
        return [
            [['id', 'merchant_id', 'store_id'], 'required'],
            [['enable'], 'integer']
        ];
    }

    public function set(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $notification = Notifications::findOne([
                "id"          => $this->id,
                "ss_mch_id"   => $this->merchant_id,
                "ss_store_id" => $this->store_id
            ]);
            if(!$notification){
                throw new \Exception("设置信息不存在");
            }

            $notification->enable     = $this->enable ? 1 : 0;
            $notification->updated_at = time();
            if(!$notification->save()){
                throw new \Exception($this->responseErrorMsg($notification));
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, []);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}