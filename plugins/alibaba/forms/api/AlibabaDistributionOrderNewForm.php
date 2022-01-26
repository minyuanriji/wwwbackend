<?php

namespace app\plugins\alibaba\forms\api;

use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionGoodsSku;
use app\plugins\shopping_voucher\models\ShoppingVoucherTargetAlibabaDistributionGoods;
use app\plugins\shopping_voucher\models\ShoppingVoucherUser;

class AlibabaDistributionOrderNewForm extends BaseModel{

    public $list; //结构[{"goods":57, "sku":11, "num":1}, ...]
    public $address; //结构{"name":"收货人","mobile":"手机", "detail":"详细地址"}
    public $remark;

    public function rules(){
        return [
            [['list'], 'required'],
            [['remark'], 'safe']
        ];
    }

    /**
     * 使用购物券抵扣运费的比例
     * @return float
     */
    public static function getShoppingVoucherDecodeExpressRate(){
        return 0.1;
    }

    /**
     * 获取商品列表信息
     * @return array
     * @throws \Exception
     */
    protected function getGoodsList(){
        $list = @json_decode($this->list, true);
        if(!$list || !is_array($list)){
            throw new \Exception("参数“list”格式不正确");
        }
        $goodsList = [];
        foreach($list as $item){
            $goods = AlibabaDistributionGoodsList::findOne($item['goods']);
            if(!$goods || $goods->is_delete){
                throw new \Exception("商品[ID:".$item['goods']."]已下架或不存在");
            }

            $goodsItem = [
                "id"          => $goods->id,
                "mall_id"     => $goods->mall_id,
                "app_id"      => $goods->app_id,
                "name"        => $goods->name,
                "cover_url"   => $goods->cover_url,
                "ali_offerId" => $goods->ali_offerId,
                "num"         => max(1, (int)$item['num'])
            ];

            $skuInfos = @json_decode($goods->sku_infos, true);
            if((empty($skuInfos) || empty($skuInfos['group'])) && $item['sku'] == "DEF"){ //无规格商品
                $goodsItem['ali_sku']       = 0;
                $goodsItem['ali_num']       = 1;
                $goodsItem['ali_spec_id']   = '';
                $goodsItem['price']         = (float)$goods->price;
                $goodsItem['freight_price'] = (float)$goods->freight_price;
                $goodsItem['sku_id']        = 0;
                $goodsItem['sku_labels']    = ['默认规格'];
            }else{
                $sku = AlibabaDistributionGoodsSku::findOne($item['sku']);
                if(!$sku || $sku->is_delete || $sku->goods_id != $goods->id){
                    throw new \Exception("规格[ID:".$item['sku']."]不存在");
                }

                $attrs = explode(",", $sku->ali_attributes);
                $labels = [];
                foreach($attrs as $attr){
                    $part = explode(":", $attr);
                    if(isset($skuInfos['group'][$part[0]]) && isset($skuInfos['values'][$attr])){
                        $labels[] = $skuInfos['group'][$part[0]]['attributeName'] . ":" . $skuInfos['values'][$attr];
                    }else{
                        $labels[] = "-:-";
                    }
                }
                $goodsItem['ali_sku']       = $sku->ali_sku_id;
                $goodsItem['ali_num']       = $sku->ali_num;
                $goodsItem['ali_spec_id']   = $sku->ali_spec_id;
                $goodsItem['price']         = (float)$sku->price;
                $goodsItem['freight_price'] = (float)$sku->freight_price;
                $goodsItem['sku_id']        = $sku->id;
                $goodsItem['sku_labels']    = !empty($sku->name) ? [$sku->name] : $labels;
            }

            $goodsItem['total_original_price'] = $goodsItem['num'] * $goodsItem['price'];
            $goodsItem['total_price'] = $goodsItem['total_original_price'];
            $goodsList[] = $goodsItem;
        }
        return $goodsList;
    }

    /**
     * 获取订单数据
     * @return array
     * @throws \Exception
     */
    protected function getData(){
        $mainData = [];

        $goodsList = $this->getGoodsList();
        if(!$goodsList){
            throw new \Exception("商品不能为空");
        }

        $orderItem = [];
        $orderItem["goods_list"] = $goodsList;

        //收货地址
        $userAddressTpl = [
            "name"     => "",
            "mobile"   => "",
            "detail"   => ""
        ];
        $userAddress = !empty($this->address) ? array_merge($userAddressTpl, @json_decode($this->address, true)) : $userAddressTpl;

        //计算对应的购物券兑换价格
        foreach($orderItem["goods_list"] as $key => $goodsItem){
            $voucherGoods = ShoppingVoucherTargetAlibabaDistributionGoods::findOne([
                "goods_id"  => $goodsItem['id'],
                "sku_id"    => (int)$goodsItem['sku_id'],
                "is_delete" => 0
            ]);
            if(!$voucherGoods){
                throw new \Exception("[".$goodsItem['id'].":".$goodsItem['sku_id']."]非购物券兑换商品");
            }

            //商品对应购物券价
            $ratio = $voucherGoods->voucher_price/$goodsItem['price'];
            $orderItem["goods_list"][$key]['sv_price']       = floatval($goodsItem['total_price']) * $ratio;
            $orderItem["goods_list"][$key]['sv_total_price'] = $orderItem["goods_list"][$key]['sv_price'] * $goodsItem['num'];
            $orderItem["goods_list"][$key]['sv_radio']       = $ratio;

            //运费对应购物券价
            $ratio = static::getShoppingVoucherDecodeExpressRate(); //运费抵扣比例
            $orderItem["goods_list"][$key]['sv_freight_price'] = $goodsItem['freight_price'] * (1/$ratio);
        }

        //获取最高运费
        $orderItem['express_price'] = $orderItem['sv_express_price'] = 0;
        $orderItem['total_goods_original_price'] = $orderItem['sv_total_goods_original_price'] = 0;
        foreach($orderItem["goods_list"] as $key => $goodsItem){
            $orderItem['total_goods_original_price']    += $goodsItem['total_price'];
            $orderItem['sv_total_goods_original_price'] += $goodsItem['sv_total_price'];
            $orderItem['express_price']                  = max($goodsItem['freight_price'], $orderItem['express_price']);
            $orderItem['sv_express_price']               = max($goodsItem['sv_freight_price'], $orderItem['sv_express_price']);
        }

        //保存原始运费价格
        $orderItem['express_origin_price']    = $orderItem['express_price'];
        $orderItem['sv_express_origin_price'] = $orderItem['sv_express_price'];

        //购物券抵扣商品
        $orderItem['shopping_voucher_use_num']              = 0;
        $orderItem['shopping_voucher_decode_price']         = 0;
        $orderItem['shopping_voucher_express_use_num']      = 0;
        $orderItem['shopping_voucher_express_decode_price'] = 0;
        $userRemainingShoppingVoucher = (float)ShoppingVoucherUser::find()->where([
            "user_id" => \Yii::$app->user->id
        ])->select("money")->scalar();
        $mainData['shopping_voucher'] = [
            "total"        => $userRemainingShoppingVoucher,
            "remaining"    => $userRemainingShoppingVoucher,
            "use_num"      => 0,
            "decode_price" => 0
        ];
        foreach($orderItem['goods_list'] as $key => $goodsItem){
            $remaining = $mainData['shopping_voucher']['remaining'];
            if($remaining > $goodsItem['sv_total_price']){
                $useNum = $goodsItem['sv_total_price'];
                $decodePrice = ($useNum/$goodsItem['sv_radio']);;
                $remaining -= $goodsItem['sv_total_price'];
                $orderItem['goods_list'][$key]['total_price'] = 0;
                $orderItem['goods_list'][$key]['sv_total_price'] = 0;
            }else{
                $useNum = $remaining;
                $decodePrice = ($useNum/$goodsItem['sv_radio']);
                $orderItem['goods_list'][$key]['sv_total_price'] -= $remaining;
                $orderItem['goods_list'][$key]['total_price']    -= $decodePrice;
                $remaining = 0;
            }

            $orderItem['shopping_voucher_decode_price'] += $decodePrice;
            $orderItem['shopping_voucher_use_num']      += $useNum;

            $mainData['shopping_voucher']['use_num']       += $useNum;
            $mainData['shopping_voucher']['decode_price']  += $decodePrice;
            $mainData['shopping_voucher']['remaining']      = $remaining;
        }

        //购物券抵扣运费
        if($mainData['shopping_voucher']['remaining'] > $orderItem['sv_express_price']){
            $orderItem['shopping_voucher_express_decode_price'] = $orderItem['express_price'];
            $orderItem['shopping_voucher_express_use_num']      = $orderItem['sv_express_price'];
            $orderItem['express_price']                         = $orderItem['sv_express_price']  = 0;
            $mainData['shopping_voucher']['remaining']         -= $orderItem['sv_express_price'];
        }else{
            $remaining = $mainData['shopping_voucher']['remaining'];
            $decodePrice = ($remaining/static::getShoppingVoucherDecodeExpressRate());
            $orderItem['shopping_voucher_express_decode_price'] = $decodePrice;
            $orderItem['shopping_voucher_express_use_num']      = $remaining;
            $orderItem['sv_express_price']                     -= $remaining;
            $orderItem['express_price']                        -= $decodePrice;
            $mainData['shopping_voucher']['remaining']          = 0;
        }

        //计算订单商品初始总金额
        $orderItem['total_price'] = $orderItem['sv_total_price'] = 0;
        foreach($orderItem['goods_list'] as $goodsItem){
            $orderItem['total_price']    += $goodsItem['total_price'];
            $orderItem['sv_total_price'] += $goodsItem['sv_total_price'];
        }
        $orderItem['total_price']    += $orderItem['express_price'];
        $orderItem['sv_total_price'] += $orderItem['sv_express_price'];

        //订单数据
        $mainData['total_price']         = 0;
        $mainData['sv_total_price']      = 0;
        $mainData["remark"]              = $this->remark;
        $mainData['list']                = [$orderItem];
        $mainData['user_address_enable'] = true;
        $mainData['user_address']        = $userAddress;

        //统计订单待支付总金额
        $mainData['total_origin_price'] = $mainData['sv_total_origin_price'] = 0;
        foreach($mainData['list'] as $orderItem){
            $mainData['total_price']    += $orderItem['total_price'];
            $mainData['sv_total_price'] += $orderItem['sv_total_price'];
        }

        $mainData['total_price']           = round($mainData['total_price'], 2);
        $mainData['total_origin_price']    = $mainData['total_price'] + $mainData['shopping_voucher']['decode_price'];
        $mainData['sv_total_price']        = round($mainData['sv_total_price'], 2);
        $mainData['sv_total_origin_price'] = $mainData['sv_total_price'] + $mainData['shopping_voucher']['use_num'];

        return $mainData;
    }

    /**
     * 生成订单号
     * @return string
     */
    public static function getOrderNo(){
        return generate_order_no("ALIS");
    }
}