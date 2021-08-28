<?php

namespace app\plugins\giftpacks\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksItem;

class GiftpacksSaveItemForm extends BaseModel{

    public $name;
    public $cover_pic;
    public $item_price;
    public $pack_id;
    public $store_id;
    public $goods_id;
    public $expired_at;
    public $max_stock;
    public $usable_times;
    public $limit_time;

    public function rules(){
        return [
            [['name', 'cover_pic', 'item_price', 'pack_id', 'store_id',
              'goods_id', 'expired_at', 'max_stock', 'usable_times'], 'required'],
            [['limit_time'], 'safe']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $giftpacks = Giftpacks::findOne($this->pack_id);
            if(!$giftpacks || $giftpacks->is_delete){
                throw new \Exception("大礼包不存在");
            }

            if($this->max_stock < $giftpacks->max_stock){
                throw new \Exception("商品库存不能低于大礼包库存");
            }

            $uniqueData = [
                "mall_id"  => \Yii::$app->mall->id,
                "pack_id"  => $this->pack_id,
                "store_id" => $this->store_id,
                "goods_id" => $this->goods_id,
            ];
            $item = GiftpacksItem::findOne($uniqueData);
            if(!$item){
                $item = new GiftpacksItem($uniqueData);
            }

            //总结算价不能大于大礼包价格
            $totalItemPrice = $this->item_price + (float)GiftpacksItem::find()->where([
                "is_delete" => 0,
                "pack_id"   => $giftpacks->id
            ])->andWhere(["NOT IN", "id", [$item ? $item->id : 0]])->sum("item_price");
            if($totalItemPrice > $giftpacks->price){
                throw new \Exception("总结算价不能大于大礼包价：" . $giftpacks->price);
            }

            $item->name         = $this->name;
            $item->cover_pic    = $this->cover_pic;
            $item->item_price   = $this->item_price;
            $item->created_at   = time();
            $item->updated_at   = time();
            $item->expired_at   = !empty($this->expired_at) ? strtotime($this->expired_at) : 0;
            $item->max_stock    = (int)$this->max_stock;
            $item->usable_times = (int)$this->usable_times;
            $item->limit_time   = (int)$this->limit_time;

            if(!$item->save()){
                throw new \Exception($this->responseErrorMsg($item));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '保存成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}