<?php

namespace app\plugins\alibaba\forms\api;

use app\models\BaseModel;
use app\models\UserAddress;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;

class AlibabaDistributionOrderForm extends BaseModel{

    public $list; //结构[{goods:xxx,num:111},...]
    public $use_shopping_voucher;
    public $use_address_id;
    public $number;

    public function rules(){
        return [
            [['list'], 'required'],
            [['use_address_id'], 'integer'],
            [['use_shopping_voucher'], 'number', 'min' => 0],
            [['number'], 'number', 'min' => 1]
        ];
    }

    protected function getGoodsList(){
        $list = @json_decode($this->list, true);
        if(!$list || !is_array($list)){
            throw new \Exception("参数list格式不正确");
        }
        $goodsList = [];
        foreach($list as $item){
            $goods = AlibabaDistributionGoodsList::findOne($item['goods']);
            if(!$goods || $goods->is_delete){
                throw new \Exception("商品[ID:".$item['goods']."]已下架或不存在");
            }
            $goodsList[] = [
                "id"          => $goods->id,
                "mall_id"     => $goods->mall_id,
                "app_id"      => $goods->app_id,
                "name"        => $goods->name,
                "cover_url"   => $goods->cover_url,
                "ali_offerId" => $goods->ali_offerId,
                "price"       => $goods->price,
                "num"         => max(1, (int)$item['num'])
            ];
        }
        print_r($goodsList);
        exit;
    }

    protected function getOrderData(){

        $goodsList = $this->getGoodsList();


        //计算订单商品初始总金额
        $orderData['total_goods_price'] = 0;
        foreach($list as $key => $item){
            $orderData['total_goods_price'] += $item[''];
        }
        $orderData['total_price'] = $orderData['total_goods_price'];



        //快递运费
        $orderData['freight'] = 0;
        $orderData['user_address'] = [];
        $orderData['user_address_enable'] = true;
        if($this->use_address_id){
            $userAddress = UserAddress::findOne($this->use_address_id);
        }else{
            $userAddress = UserAddress::getUserAddressDefault([
                'user_id'   => \Yii::$app->user->id,
                'is_delete' => 0,
            ]);
        }
        if($userAddress){
            $orderData['user_address'] = $userAddress->getAttributes();
        }



        return $orderData;
    }
}