<?php
namespace app\plugins\baopin\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\baopin\models\BaopinMchGoods;

class StoreGoodsStockEditForm extends BaseModel{

    public $goods_id;
    public $store_id;
    public $stock_num;

    public function rules(){
        return [
            [['goods_id', 'store_id'], 'required'],
            [['stock_num'], 'number', 'min' => 0]
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $baopinMchGoods = BaopinMchGoods::findOne([
                "store_id" => $this->store_id,
                "goods_id" => $this->goods_id
            ]);
            if(!$baopinMchGoods || $baopinMchGoods->is_delete){
                throw new \Exception("获取不到记录信息");
            }
            $baopinMchGoods->stock_num = (int)$this->stock_num;
            if(!$baopinMchGoods->save()){
                throw new \Exception($this->responseErrorMsg($baopinMchGoods));
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