<?php

namespace app\plugins\smart_shop\components;

use app\models\User;
use app\models\UserRelationshipLink;
use app\plugins\smart_shop\models\KpiLinkCoupon;
use app\plugins\smart_shop\models\KpiLinkGoods;
use app\plugins\smart_shop\models\KpiNewOrder;
use app\plugins\smart_shop\models\KpiRegister;
use app\plugins\smart_shop\models\Setting;
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

            //KPI奖励
            $awardPoint = 0;
            $startTime = strtotime(date("Y-m-d") . " 00:00:00");
            $setting = $this->getKPISetting($merchant_id);
            $query = KpiRegister::find()->andWhere([
                "AND",
                ["mall_id" => $inviterUser->mall_id],
                "inviter_user_id" => $inviterUser->id,
                'store_id' => $store_id,
                'merchant_id' => $merchant_id,
                [">", "created_at", $startTime],
                ["<", "created_at", ($startTime + 3600 * 24 - 1)]
            ]);
            $totalPoint = (int)$query->sum("point");
            $userNum = (int)$query->count() + 1;
            if(isset($setting['new_user_rules']) && !empty($setting['new_user_rules'])){
                $rule = $this->matchRule($setting['new_user_rules'], "user_num", $userNum);
                if($rule){
                    $awardPoint = $rule['award_point'];
                }
            }
            if(isset($setting['new_user_day_limit']) && $setting['new_user_day_limit']){
                $awardPoint = min($awardPoint, max(0, $setting['new_user_day_limit_point'] - $totalPoint));
            }

            $kpiRegister = new KpiRegister([
                "mall_id"         => $inviterUser->mall_id,
                "inviter_user_id" => $inviterUser->id,
                "user_id_list"    => implode(",", $parentIds),
                "created_at"      => time(),
                "mobile"          => !empty($user->mobile) ? $user->mobile : "none",
                'store_id'        => $store_id,
                'merchant_id'     => $merchant_id,
                "point"           => $awardPoint
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
     * 分享商品链接访问统计
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

            //KPI奖励
            $awardPoint = 0;
            $startTime = strtotime(date("Y-m-d") . " 00:00:00");
            $setting = $this->getKPISetting($shopData['merchant_id']);
            $query = KpiLinkGoods::find()->andWhere([
                "AND",
                ["mall_id" => $inviterUser->mall_id],
                "inviter_user_id" => $inviterUser->id,
                'store_id' => $shopData['ss_store_id'],
                'merchant_id' => $shopData['merchant_id'],
                [">", "created_at", $startTime],
                ["<", "created_at", ($startTime + 3600 * 24 - 1)]
            ]);
            $totalPoint = (int)$query->sum("point");
            $shareNum = (int)$query->count() + 1;
            if(isset($setting['share_rules']) && !empty($setting['share_rules'])){
                $rule = $this->matchRule($setting['share_rules'], "share_num", $shareNum);
                if($rule){
                    $awardPoint = $rule['award_point'];
                }
            }
            if(isset($setting['share_day_limit']) && $setting['share_day_limit']){
                $awardPoint = min($awardPoint, max(0, $setting['share_day_limit_point'] - $totalPoint));
            }

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
                "point"           => $awardPoint
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
     * 分享优惠券链接访问统计
     * @param $data
     *  [
     *    'store_id'       => '门店ID',
     *    'merchant_id'    => '商户ID',
     *    'inviter_mobile' => '邀请人手机号',
     *    'mobile'         => '访问者手机号',
     *  ]
     * @return boolean
     */
    public function linkCouponList($data){

        $smartShop = new SmartShop();
        $shopData = $smartShop->getStoreDetail($data['store_id']);
        if(!$shopData){
            $this->error = "无法获取门店信息";
            return false;
        }

        $exists = KpiLinkCoupon::findOne([
            "mobile"          => !empty($data['mobile']) ? $data['mobile'] : "none",
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

            //KPI奖励
            $awardPoint = 0;
            $startTime = strtotime(date("Y-m-d") . " 00:00:00");
            $setting = $this->getKPISetting($shopData['merchant_id']);
            $query = KpiLinkCoupon::find()->andWhere([
                "AND",
                ["mall_id" => $inviterUser->mall_id],
                "inviter_user_id" => $inviterUser->id,
                'store_id' => $shopData['ss_store_id'],
                'merchant_id' => $shopData['merchant_id'],
                [">", "created_at", $startTime],
                ["<", "created_at", ($startTime + 3600 * 24 - 1)]
            ]);
            $totalPoint = (int)$query->sum("point");
            $shareNum = (int)$query->count() + 1;
            if(isset($setting['share_rules']) && !empty($setting['share_rules'])){
                $rule = $this->matchRule($setting['share_rules'], "share_num", $shareNum);
                if($rule){
                    $awardPoint = $rule['award_point'];
                }
            }
            if(isset($setting['share_day_limit']) && $setting['share_day_limit']){
                $awardPoint = min($awardPoint, max(0, $setting['share_day_limit_point'] - $totalPoint));
            }

            $kpiLinkCoupon = new KpiLinkCoupon([
                "mall_id"         => $inviterUser->mall_id,
                "inviter_user_id" => $inviterUser->id,
                "user_id_list"    => implode(",", $parentIds),
                "created_at"      => time(),
                "mobile"          => !empty($data['mobile']) ? $data['mobile'] : "none",
                "date"            => date("Ymd"),
                'store_id'        => $shopData['ss_store_id'],
                'merchant_id'     => $shopData['merchant_id'],
                "point"           => $awardPoint
            ]);

            if(!$kpiLinkCoupon->save()){
                throw new \Exception(json_encode($kpiLinkCoupon->getErrors()));
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

            //KPI奖励
            $awardPoint = 0;
            $startTime = strtotime(date("Y-m-d") . " 00:00:00");
            $setting = $this->getKPISetting($merchant_id);
            $query = KpiNewOrder::find()->andWhere([
                "AND",
                ["mall_id" => $inviterUser->mall_id],
                "inviter_user_id" => $inviterUser->id,
                'store_id' => $store_id,
                'merchant_id' => $merchant_id,
                [">", "created_at", $startTime],
                ["<", "created_at", ($startTime + 3600 * 24 - 1)]
            ]);
            $totalPoint = (int)$query->sum("point");
            $orderNum = (int)$query->count() + 1;
            if(isset($setting['new_order_rules']) && !empty($setting['new_order_rules'])){
                $rule = $this->matchRule($setting['new_order_rules'], "order_num", $orderNum);
                if($rule){
                    $awardPoint = $rule['award_point'];
                }
            }
            if(isset($setting['new_order_day_limit']) && $setting['new_order_day_limit']){
                $awardPoint = min($awardPoint, max(0, $setting['new_order_day_limit_point'] - $totalPoint));
            }

            $kpiNewOrder = new KpiNewOrder([
                "mall_id"         => $inviterUser->mall_id,
                "inviter_user_id" => $inviterUser->id,
                "user_id_list"    => implode(",", $parentIds),
                "created_at"      => time(),
                "mobile"          => !empty($mobile) ? $mobile : "none",
                "store_id"        => $store_id,
                "merchant_id"     => $merchant_id,
                "source_table"    => $order_type,
                "source_id"       => $order_id,
                "point"           => $awardPoint
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

    /**
     * 获取奖励设置
     * @return array|mixed
     */
    private function getKPISetting($merchant_id){
        $setting = Setting::findOne([
            "key"     => "kpi_setting_" . $merchant_id,
            "mall_id" => \Yii::$app->mall->id
        ]);
        $data = [];
        if($setting){
            $data = !empty($setting->value) ? json_decode($setting->value, true) : [];
        }
        return $data;
    }

    /**
     * 匹配规则
     * @param $rules
     * @param $key
     * @param $num
     * @return array
     */
    private function matchRule($rules, $key, $num){
        $this->sortRules($rules, $key);
        $rules = array_reverse($rules);
        $match = null;
        foreach($rules as $rule){
            if($num >= $rule[$key]){
                $match = $rule;
                break;
            }
        }
        if(!$match){
            $match = !empty($rules) ? array_pop($rules) : null;
        }
        return $match;
    }

    /**
     * 规则排序
     * @param $rules
     */
    private function sortRules(&$rules, $key){
        $temps = [];
        foreach($rules as $rule){
            $temps[$rule[$key]] = $rule;
        }
        $rules = [];
        ksort($temps);
        foreach($temps as $rule){
            $rules[] = $rule;
        }
    }
}