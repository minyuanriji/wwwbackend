<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromGiftpacks;

class FromGiftpacksSaveForm extends BaseModel{

    public $is_open;
    public $give_type;
    public $give_value;
    public $start_at;
    public $recommender;
    public $pack_id;

    public function rules(){
        return [
            [['give_value','start_at'], 'required'],
            [['is_open', 'pack_id', 'give_type'], 'integer'],
            [['recommender'], 'safe'],
        ];
    }

    public function save(){

        if(!$this->validate())
            return $this->responseErrorInfo();

        try {
            $fromGiftPacks = ShoppingVoucherFromGiftpacks::findOne(['pack_id' => $this->pack_id]);
            if(!$fromGiftPacks){
                $fromGiftPacks = new ShoppingVoucherFromGiftpacks([
                    "mall_id"    => \Yii::$app->mall->id,
                    "created_at" => time()
                ]);
            }else{
                $fromGiftPacks->is_delete = 0;
                $fromGiftPacks->deleted_at = 0;
            }

            $fromGiftPacks->pack_id     = $this->pack_id;
            $fromGiftPacks->give_type   = $this->give_type;
            $fromGiftPacks->give_value  = max(min($this->give_value, 100), 0);
            $fromGiftPacks->updated_at  = time();
            $fromGiftPacks->start_at    = max(time(), strtotime($this->start_at));
            $fromGiftPacks->recommender = @json_encode($this->recommender);

            if(!$fromGiftPacks->save())
                throw new \Exception($this->responseErrorMsg($fromGiftPacks));

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '保存成功');
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}