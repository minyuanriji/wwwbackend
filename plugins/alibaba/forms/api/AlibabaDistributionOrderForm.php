<?php

namespace app\plugins\alibaba\forms\api;

use app\models\BaseModel;
use app\models\UserAddress;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\shopping_voucher\models\ShoppingVoucherTargetAlibabaDistributionGoods;
use app\plugins\shopping_voucher\models\ShoppingVoucherTargetGoods;
use app\plugins\shopping_voucher\models\ShoppingVoucherUser;

class AlibabaDistributionOrderForm extends BaseModel{

    public $list; //结构[{"goods":57,"num":1}, ...]
    public $use_shopping_voucher;
    public $use_address_id;
    public $number;
    public $remark;

    public function rules(){
        return [
            [['list'], 'required'],
            [['use_address_id'], 'integer'],
            [['use_shopping_voucher'], 'number', 'min' => 0],
            [['number'], 'number', 'min' => 1],
            [['remark'], 'safe']
        ];
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
                "price"       => (float)$goods->price,
                "num"         => max(1, (int)$item['num'])
            ];
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

        $goodsList = $this->getGoodsList();
        if(!$goodsList){
            throw new \Exception("商品不能为空");
        }

        $orderItem = [];
        $orderItem["goods_list"] = $goodsList;


        //计算订单商品初始总金额
        $orderItem['total_price'] = 0;
        foreach($goodsList as $goodsItem){
            $orderItem['total_price'] += floatval($goodsItem['price'] * $goodsItem['num']);
        }
        $orderItem['total_goods_original_price'] = $orderItem['total_price'];

        //快递运费
        $userAddress = [];
        if($this->use_address_id){
            $userAddress = UserAddress::findOne($this->use_address_id);
        }else{
            $userAddress = UserAddress::getUserAddressDefault([
                'user_id'   => \Yii::$app->user->id,
                'is_delete' => 0,
            ]);
        }
        $orderItem['express_price'] = 0;

        //订单数据
        $mainData = [];
        $mainData['total_price'] = 0;
        $mainData["remark"] = $this->remark;
        $mainData['list'] = [$orderItem];
        if($userAddress){
            $mainData['user_address'] = $userAddress->getAttributes();
        }
        $mainData['user_address_enable'] = true;
        $mainData['user_address'] = $userAddress;

        //使用购物券
        $userRemainingShoppingVoucher = (float)ShoppingVoucherUser::find()->where([
            "user_id" => \Yii::$app->user->id
        ])->select("money")->scalar();
        $mainData['shopping_voucher'] = [
            "decode_price" => 0,
            "enable"       => true,
            "remaining"    => $userRemainingShoppingVoucher,
            "total"        => $userRemainingShoppingVoucher,
            "use"          => $this->use_shopping_voucher ? true : false,
            "use_num"      => 0
        ];
        foreach($mainData['list'] as &$orderItem){
            $orderItem['shopping_voucher_use_num'] = 0;
            $orderItem['shopping_voucher_decode_price'] = 0;
            foreach($orderItem['goods_list'] as &$goodsItem){
                $goodsItem['use_shopping_voucher'] = 0;
                if($this->use_shopping_voucher && $goodsItem['total_price'] > 0){
                    $voucherGoods = ShoppingVoucherTargetAlibabaDistributionGoods::findOne([
                        "goods_id"  => $goodsItem['id'],
                        "is_delete" => 0
                    ]);
                    if(!$voucherGoods) continue;
                    $goodsItem['use_shopping_voucher'] = 1;
                    //计算购物券价与商品价格比例
                    $ratio = $voucherGoods->voucher_price/$goodsItem['price'];
                    if(($userRemainingShoppingVoucher/$ratio) > $goodsItem['total_price']){
                        $needNum = floatval($goodsItem['total_price']) * $ratio;
                        $goodsItem['use_shopping_voucher_decode_price'] = $goodsItem['total_price'];
                        $userRemainingShoppingVoucher -= $needNum;
                        $goodsItem['use_shopping_voucher_num'] = $needNum;
                        $goodsItem['total_price'] = 0;
                    }else{
                        $decodePrice = round(($userRemainingShoppingVoucher/$ratio), 2);
                        $goodsItem['total_price'] -= $decodePrice;
                        $goodsItem['use_shopping_voucher_decode_price'] = $decodePrice;
                        $goodsItem['use_shopping_voucher_num'] = $userRemainingShoppingVoucher;
                        $userRemainingShoppingVoucher = 0;
                    }
                    $orderItem['shopping_voucher_decode_price'] += $goodsItem['use_shopping_voucher_decode_price'];
                    $orderItem['shopping_voucher_use_num'] += $goodsItem['use_shopping_voucher_num'];
                }
            }
            $orderItem['total_price'] -= round($orderItem['shopping_voucher_decode_price'], 2);
        }
        $mainData['shopping_voucher']['remaining'] = $userRemainingShoppingVoucher;

        //统计订单待支付总金额
        foreach($mainData['list'] as $orderItem){
            $mainData['total_price'] += $orderItem['total_price'] + $orderItem['express_price'];
        }
        $mainData['total_price'] = round($mainData['total_price'], 2);

        return $mainData;
    }
}