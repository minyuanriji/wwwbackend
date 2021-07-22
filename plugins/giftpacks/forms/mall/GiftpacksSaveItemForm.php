<?php

namespace app\plugins\giftpacks\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\giftpacks\models\GiftpacksItem;

class GiftpacksSaveItemForm extends BaseModel{

    public $name;
    public $cover_pic;
    public $pack_id;
    public $store_id;
    public $goods_id;
    public $expired_at;
    public $max_stock;
    public $usable_times;

    public function rules(){
        return [
            [['name', 'cover_pic', 'pack_id', 'store_id',
              'goods_id', 'expired_at', 'max_stock', 'usable_times'], 'required']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $item = GiftpacksItem::findOne([
                "pack_id"  => $this->pack_id,
                "store_id" => $this->store_id,
                "goods_id" => $this->goods_id,
            ]);
            if(!$item){
                $item = new GiftpacksItem([
                    "mall_id"  => \Yii::$app->mall->id,
                    "pack_id"  => $this->pack_id,
                    "store_id" => $this->store_id,
                    "goods_id" => $this->goods_id,
                ]);
            }

            $item->name       = $this->name;
            $item->cover_pic  = $this->cover_pic;
            $item->created_at = time();
            $item->updated_at = time();
            $item->expired_at = !empty($this->expired_at) ? strtotime($this->expired_at) : 0;
            $item->max_stock  = (int)$this->max_stock;
            $item->usable_times = (int)$this->usable_times;

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