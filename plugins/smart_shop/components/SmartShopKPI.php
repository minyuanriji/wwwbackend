<?php

namespace app\plugins\smart_shop\components;

use app\models\User;
use app\models\UserRelationshipLink;
use app\plugins\smart_shop\models\KpiLinkGoods;
use app\plugins\smart_shop\models\KpiNewOrder;
use app\plugins\smart_shop\models\KpiRegister;
use yii\base\Component;

class SmartShopKPI extends Component{

    private $error;

    public function getError(){
        return $this->error;
    }

    /**
     * 成为会员
     * @param User $inviter 邀请者用户信息
     * @param User $user 被邀请用户信息
     * @param $store_id 门店ID
     * @param $merchant_id 小程序商户ID
     * @throws \Exception
     * @return boolean
     */
    public function register(User $inviterUser, User $user, $store_id, $merchant_id){

        //已有上级或者上级是自己的不进行处理
        if(($user->parent_id && $user->parent_id != GLOBAL_PARENT_ID) || $user->id == $inviterUser->id || $user->mobile == $inviterUser->mobile)
            return true;

        try {

            $relatLink = UserRelationshipLink::findOne(["user_id" => $inviterUser->id]);
            if(!$relatLink){
                throw new \Exception("邀请用户关系链异常");
            }

            $parentIds = array_merge([$inviterUser->id], $relatLink->getParentIds());
            sort($parentIds);

            $kpiRegister = new KpiRegister([
                "mall_id"         => $inviterUser->mall_id,
                "inviter_user_id" => $inviterUser->id,
                "user_id_list"    => implode(",", $parentIds),
                "created_at"      => time(),
                "mobile"          => !empty($user->mobile) ? $user->mobile : "none",
                'store_id'        => $store_id,
                'merchant_id'     => $merchant_id
            ]);

            if(!$kpiRegister->save()){
                throw new \Exception(json_encode($kpiRegister->getErrors()));
            }

        }catch (\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }

        return true;
    }

    /**
     * 分享链接访问统计
     * @param $data
     *  [
     *    'store_id'       => '门店ID',
     *    'merchant_id'    => '商户ID',
     *    'inviter_mobile' => '邀请人手机号',
     *    'mobile'         => '访问者手机号',
     *    'goods_id'       => '商品ID'
     *  ]
     * @return boolean
     */
    public function linkGoodsDetail($data){

        $smartShop = new SmartShop();
        $shopData = $smartShop->getStoreDetail($data['store_id']);
        if(!$shopData){
            $this->error = "无法获取门店信息";
            return false;
        }

        $exists = KpiLinkGoods::findOne([
            "mobile"          => !empty($data['mobile']) ? $data['mobile'] : "none",
            "goods_id"        => $data['goods_id'],
            "date"            => date("Ymd"),
            'store_id'        => $shopData['ss_store_id'],
            'merchant_id'     => $shopData['merchant_id'],
        ]);
        if($exists){
            return true;
        }

        try {

            //获取邀请者本地用户
            $inviterUser = User::findOne(["mobile" => $data['inviter_mobile']]);
            if(!$inviterUser){
                throw new \Exception("邀请者用户信息不存在");
            }

            $relatLink = UserRelationshipLink::findOne(["user_id" => $inviterUser->id]);
            if(!$relatLink){
                throw new \Exception("邀请用户关系链异常");
            }

            $parentIds = array_merge([$inviterUser->id], $relatLink->getParentIds());
            sort($parentIds);

            $kpiLinkGoods = new KpiLinkGoods([
                "mall_id"         => $inviterUser->mall_id,
                "inviter_user_id" => $inviterUser->id,
                "user_id_list"    => implode(",", $parentIds),
                "created_at"      => time(),
                "mobile"          => !empty($data['mobile']) ? $data['mobile'] : "none",
                "goods_id"        => $data['goods_id'],
                "date"            => date("Ymd"),
                'store_id'        => $shopData['ss_store_id'],
                'merchant_id'     => $shopData['merchant_id'],
            ]);

            if(!$kpiLinkGoods->save()){
                throw new \Exception(json_encode($kpiLinkGoods->getErrors()));
            }

        }catch (\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }

        return true;
    }

    /**
     * 新订单统计
     * @param $store_id 门店ID
     * @param $merchant_id 小程序商户ID
     * @param $order_type 订单类型（cyorder|czorder）
     * @param $order_id 订单ID
     * @param $mobile 支付手机号
     * @param $inviter_mobile 邀请人手机号
     * @throws \Exception
     * @return boolean
     */
    public function newOrder($order_type, $store_id, $merchant_id,  $order_id, $mobile, $inviter_mobile){

        try {
            //获取邀请者本地用户
            $inviterUser = User::findOne(["mobile" => $inviter_mobile]);
            if(!$inviterUser){
                throw new \Exception("邀请者用户信息不存在");
            }

            $relatLink = UserRelationshipLink::findOne(["user_id" => $inviterUser->id]);
            if(!$relatLink){
                throw new \Exception("邀请用户关系链异常");
            }

            $parentIds = array_merge([$inviterUser->id], $relatLink->getParentIds());
            sort($parentIds);

            $kpiNewOrder = new KpiNewOrder([
                "mall_id"         => $inviterUser->mall_id,
                "inviter_user_id" => $inviterUser->id,
                "user_id_list"    => implode(",", $parentIds),
                "created_at"      => time(),
                "mobile"          => !empty($mobile) ? $mobile : "none",
                "store_id"        => $store_id,
                "merchant_id"     => $merchant_id,
                "source_table"    => $order_type,
                "source_id"       => $order_id
            ]);

            if(!$kpiNewOrder->save()){
                throw new \Exception(json_encode($kpiNewOrder->getErrors()));
            }

        }catch (\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }

        return true;
    }
}