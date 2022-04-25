<?php

namespace app\plugins\smart_shop\components;

use app\models\User;
use app\models\UserRelationshipLink;
use app\plugins\smart_shop\models\KpiNewOrder;
use app\plugins\smart_shop\models\KpiRegister;
use yii\base\Component;

class SmartShopKPI extends Component{

    /**
     * 成为会员
     * @param User $inviter 邀请者用户信息
     * @param User $user 被邀请用户信息
     * @throws \Exception
     */
    public function register(User $inviterUser, User $user){

        //已有上级或者上级是自己的不进行处理
        if(($user->parent_id && $user->parent_id != GLOBAL_PARENT_ID) || $user->id == $inviterUser->id)
            return;

        try {

            $relatLink = UserRelationshipLink::findOne(["user_id" => $inviterUser->id]);
            if(!$relatLink){
                throw new \Exception("邀请用户关系链异常");
            }

            $parentIds = array_merge([$inviterUser->id], $relatLink->getParentIds());
            sort($parentIds);

            $kpiRegister = new KpiRegister([
                "mall_id"      => $inviterUser->mall_id,
                "user_id_list" => implode(",", $parentIds),
                "created_at"   => time(),
                "mobile"       => !empty($user->mobile) ? $user->mobile : "none"
            ]);

            if(!$kpiRegister->save()){
                throw new \Exception(json_encode($kpiRegister->getErrors()));
            }
        }catch (\Exception $e){

        }
    }

    /**
     * 分享链接访问统计
     * @param $data
     *  [
     *    'inviter_mobile' => '邀请人手机号',
     *    'mobile'         => '访问者手机号',
     *    'goods_id'       => '商品ID'
     *  ]
     */
    public function linkGoodsDetail($data){}

    /**
     * 新订单统计
     * @param $order_type 订单类型（cyorder|czorder）
     * @param $order_id 订单ID
     * @param $mobile 支付手机号
     * @param $inviter_mobile 邀请人手机号
     * @throws \Exception
     */
    public function newOrder($order_type, $order_id, $mobile, $inviter_mobile){

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
            "mall_id"      => $inviterUser->mall_id,
            "user_id_list" => implode(",", $parentIds),
            "created_at"   => time(),
            "mobile"       => !empty($mobile) ? $mobile : "none",
            "source_table" => $order_type,
            "source_id"    => $order_id
        ]);

        if(!$kpiNewOrder->save()){
            throw new \Exception(json_encode($kpiNewOrder->getErrors()));
        }

    }
}