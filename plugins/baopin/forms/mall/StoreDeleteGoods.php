<?php
namespace app\plugins\baopin\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\baopin\models\BaopinMchGoods;

class StoreDeleteGoods extends BaseModel{

    public $goods_id;
    public $store_id;

    public function rules(){
        return [
            [['goods_id', 'store_id'], 'required']
        ];
    }

    public function delete(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $baopinMchGoods = BaopinMchGoods::findOne([
                "store_id" => $this->store_id,
                "goods_id" => $this->goods_id
            ]);
            if(!$baopinMchGoods){
                throw new \Exception("获取不到记录信息");
            }

            $baopinMchGoods->delete();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];

        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }
}