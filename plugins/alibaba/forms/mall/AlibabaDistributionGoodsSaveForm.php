<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionGoodsSku;

class AlibabaDistributionGoodsSaveForm extends BaseModel{

    public $goods;

    public function rules(){
        return [
            [['goods'], 'required']
        ];
    }

    public function save(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $this->goods = json_decode($this->goods, true);

            $goods = AlibabaDistributionGoodsList::findOne($this->goods['id']);
            if(!$goods){
                throw new \Exception("商品不存在");
            }

            $goods->ali_category_id = implode(",", $this->goods['ali_category_id']);
            $goods->price           = $this->goods['price'];
            $goods->origin_price    = $this->goods['origin_price'];
            $goods->updated_at      = time();
            if(!$goods->save()){
                throw new \Exception($this->responseErrorMsg($goods));
            }

            foreach($this->goods['sku_list'] as $skuInfo){
                $sku = AlibabaDistributionGoodsSku::findOne([
                    "ali_sku_id" => $skuInfo['ali_sku_id']
                ]);
                if(!$sku){
                    $sku = new AlibabaDistributionGoodsSku([
                        "ali_sku_id" => $skuInfo['ali_sku_id'],
                        "goods_id"   => $goods->id,
                        "mall_id"    => \Yii::$app->mall->id,
                        "created_at" => time()
                    ]);
                    $sku->ali_spec_id          = $skuInfo['ali_spec_id'];
                    $sku->cargo_number         = $skuInfo['cargo_number'];
                    $sku->amount_on_sale       = $skuInfo['amount_on_sale'];
                    $sku->ali_price            = $skuInfo['ali_price'];
                    $sku->consign_price        = $skuInfo['consign_price'];
                    $sku->is_delete            = 0;
                    $sku->ali_attributes_label = $skuInfo['ali_attributes_label'];
                }

                $sku->price          = $skuInfo['price'];
                $sku->origin_price   = $skuInfo['origin_price'];
                $sku->updated_at     = time();
                $sku->is_delete      = 0;

                if(!$sku->save()){
                    throw new \Exception($this->responseErrorMsg($sku));
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => "保存成功"
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}