<?php

namespace app\plugins\giftpacks\forms\api;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksItem;
use app\plugins\giftpacks\models\GiftpacksOrderItem;

class GiftpacksDetailForm extends BaseModel{

    public $pack_id;

    public function rules(){
        return [
            [['pack_id'], 'required']
        ];
    }

    public function getDetail(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $giftpacks = Giftpacks::findOne($this->pack_id);
            if(!$giftpacks || $giftpacks->is_delete){
                throw new \Exception("大礼包不存在");
            }

            $detail = $giftpacks->getAttributes();

            $query = GiftpacksItem::find()->alias("gpi");
            $query->leftJoin(["goi" => GiftpacksOrderItem::tableName()], "goi.pack_item_id=gpi.id");
            $query->where([ "gpi.pack_id" => $this->pack_id, "gpi.is_delete" => 0]);
            $query->groupBy("gpi.id HAVING count(gpi.id) < gpi.max_stock");

            $detail['item_count'] = (int)$query->count();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'detail' => $detail
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }
}