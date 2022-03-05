<?php

namespace app\plugins\taobao\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taobao\models\TaobaoGoods;

class TaobaoGoodsDetailForm extends BaseModel {

    public $goods_id;

    public function rules(){
        return [
            [['goods_id'], 'required']
        ];
    }

    public function getDetail(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $taobaoGoods = TaobaoGoods::findOne(["goods_id" => $this->goods_id]);
            if(!$taobaoGoods){
                throw new \Exception("商品信息不存在");
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'detail' => $taobaoGoods->getAttributes()
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