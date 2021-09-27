<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionGoodsSku;
use app\plugins\shopping_voucher\models\ShoppingVoucherTargetAlibabaDistributionGoods;

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
            if(empty($this->goods['ali_category_id'])){
                throw new \Exception("请设置类目");
            }
            if(isset($this->goods['ali_product_info'])){
                if(!is_array($this->goods['ali_product_info'])){
                    $this->goods['ali_product_info'] = (array)@json_decode($this->goods['ali_product_info'], true);
                }
                $description = $this->goods['ali_product_info']['info']['description'];
                $productInfo = (array)@json_decode($goods->ali_product_info, true);
                $productInfo['info']['description'] = $description;
                $productInfo['info']['image'] = $this->goods['ali_product_info']['info']['image'];
                $goods->ali_product_info = json_encode($productInfo);
            }

            $goods->name              = $this->goods['name'];
            $goods->ali_category_id   = implode(",", $this->goods['ali_category_id']);
            $goods->price             = $this->goods['price'];
            $goods->price_rate        = $this->goods['price_rate'];
            $goods->origin_price      = $this->goods['origin_price'];
            $goods->origin_price_rate = $this->goods['origin_price_rate'];
            $goods->freight_price     = $this->goods['freight_price'];
            $goods->updated_at        = time();
            if(!$goods->save()){
                throw new \Exception($this->responseErrorMsg($goods));
            }

            if($this->goods['sku_list']){
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
                    }

                    $sku->origin_price   = $skuInfo['origin_price'];
                    $sku->freight_price  = $skuInfo['freight_price'];
                    $sku->updated_at     = time();
                    $sku->is_delete      = 0;
                    $sku->ali_num        = max(1, $skuInfo['ali_num']);
                    $sku->price          = floatval($goods->price_rate)/100 * $sku->ali_price * $sku->ali_num;
                    //$sku->price          = $skuInfo['price'];
                    $sku->name           = $skuInfo['name'];

                    if(!$sku->save()){
                        throw new \Exception($this->responseErrorMsg($sku));
                    }

                    //加入购物券消费场景
                    $shoppingVoucheGoods = ShoppingVoucherTargetAlibabaDistributionGoods::findOne([
                        "goods_id" => $goods->id,
                        "sku_id"   => $sku->id,
                    ]);
                    if(!$shoppingVoucheGoods){
                        $shoppingVoucheGoods = new ShoppingVoucherTargetAlibabaDistributionGoods([
                            "mall_id"    => $goods->mall_id,
                            "goods_id"   => $goods->id,
                            "sku_id"     => $sku->id,
                            "created_at" => time(),
                        ]);
                    }
                    $shoppingVoucheGoods->name          = $goods->name . "#" . $skuInfo['ali_attributes_label'];
                    $shoppingVoucheGoods->cover_pic     = $goods->cover_url;
                    $shoppingVoucheGoods->voucher_price = $sku->price;
                    $shoppingVoucheGoods->updated_at    = time();
                    $shoppingVoucheGoods->deleted_at    = 0;
                    $shoppingVoucheGoods->is_delete     = 0;
                    if(!$shoppingVoucheGoods->save()){
                        throw new \Exception($this->responseErrorMsg($shoppingVoucheGoods));
                    }
                }
            }

            //加入购物券消费场景（针对默认规格）
            $shoppingVoucheGoods = ShoppingVoucherTargetAlibabaDistributionGoods::findOne([
                "goods_id" => $goods->id,
                "sku_id"   => 0,
            ]);
            if(!$shoppingVoucheGoods){
                $shoppingVoucheGoods = new ShoppingVoucherTargetAlibabaDistributionGoods([
                    "mall_id"    => $goods->mall_id,
                    "goods_id"   => $goods->id,
                    "sku_id"     => 0,
                    "created_at" => time(),
                ]);
            }
            $shoppingVoucheGoods->name          = $goods->name;
            $shoppingVoucheGoods->cover_pic     = $goods->cover_url;
            $shoppingVoucheGoods->voucher_price = $goods->price;
            $shoppingVoucheGoods->updated_at    = time();
            $shoppingVoucheGoods->deleted_at    = 0;
            $shoppingVoucheGoods->is_delete     = 0;
            if(!$shoppingVoucheGoods->save()){
                throw new \Exception($this->responseErrorMsg($shoppingVoucheGoods));
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