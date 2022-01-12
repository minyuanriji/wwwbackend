<?php

namespace app\plugins\smart_shop\forms\mall;

use app\core\ApiCode;
use app\plugins\sign_in\forms\BaseModel;
use app\plugins\smart_shop\models\Merchant;
use app\plugins\smart_shop\models\MerchantFzlist;

class MerchantDeleteForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required'],
        ];
    }

    public function delete(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $t = \Yii::$app->db->beginTransaction();
        try {

            $merchant = Merchant::findOne($this->id);
            if(!$merchant || $merchant->is_delete){
                throw new \Exception("记录[ID:{$this->id}]不存在");
            }

            MerchantFzlist::updateAll(["is_delete" => 1], ["bsh_mch_id" => $merchant->bsh_mch_id]);

            $merchant->is_delete = 1;
            if(!$merchant->save()){
                throw new \Exception($this->responseErrorMsg($merchant));
            }

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => []
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}