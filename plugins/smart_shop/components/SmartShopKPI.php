<?php

namespace app\plugins\smart_shop\components;

use app\models\User;
use app\models\UserRelationshipLink;
use app\plugins\smart_shop\models\KpiLinkCoupon;
use app\plugins\smart_shop\models\KpiLinkGoods;
use app\plugins\smart_shop\models\KpiNewOrder;
use app\plugins\smart_shop\models\KpiRegister;
use app\plugins\smart_shop\models\KpiSetting;
use app\plugins\smart_shop\models\KpiUser;
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
    public function register(User $sourceUser, User $user, $store_id, $merchant_id){

        if($user->parent_id != $sourceUser->id)
            return;

        try {

            //获取推广员信息
            $kpiUsers = static::getKpiUserAllParents($user, $store_id, $merchant_id);
            if(!$kpiUsers || empty($kpiUsers)){
                throw new \Exception("推广员信息不存在");
            }

            $parentIds = [];
            foreach($kpiUsers as $kpiUser){
                $parentIds[] = $kpiUser->user_id;
            }

            $kpiUser = $kpiUsers[0];

            $uniqueData = [
                "mall_id"         => $kpiUser->mall_id,
                "inviter_user_id" => $kpiUser->user_id,
                "mobile"          => !empty($user->mobile) ? $user->mobile : "none"
            ];
            $kpiRegister = KpiRegister::findOne($uniqueData);
            if(!$kpiRegister){
                $kpiRegister = new KpiRegister(array_merge($uniqueData, [
                    "user_id_list"    => implode(",", $parentIds),
                    "created_at"      => time(),
                    'store_id'        => $store_id,
                    'merchant_id'     => $merchant_id,
                    "point"           => 0
                ]));

                if(!$kpiRegister->save()){
                    throw new \Exception(json_encode($kpiRegister->getErrors()));
                }

                //处理奖励
                KpiSetting::setRegisterAward($kpiRegister, $kpiUser, time());
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
     *    'goods_id'       => '商品ID',
     *    'goods_type'     => '类型'
     *  ]
     * @return boolean
     */
    public function linkGoodsDetail($data){

        $data['goods_type'] = isset($data['goods_type']) ? $data['goods_type'] : "goods";

        $smartShop = new SmartShop();
        $shopData = $smartShop->getStoreDetail($data['store_id']);
        if(!$shopData){
            $this->error = "无法获取门店信息";
            return false;
        }

        $exists = KpiLinkGoods::findOne([
            "mobile"          => !empty($data['mobile']) ? $data['mobile'] : "none",
            "goods_id"        => $data['goods_id'],
            "goods_type"      => $data['goods_type'],
            "date"            => date("Ymd"),
            'store_id'        => $shopData['ss_store_id'],
            'merchant_id'     => $shopData['merchant_id'],
        ]);

        if($exists){
            return true;
        }

        try {

            //获取邀请者本地用户
            $sourceUser = User::findOne(["mobile" => $data['inviter_mobile']]);
            if(!$sourceUser){
                throw new \Exception("邀请者用户信息不存在");
            }

            //获取推广员信息
            $kpiUsers = static::getKpiUserAllParents($sourceUser, $shopData['ss_store_id'], $shopData['merchant_id']);
            if(!$kpiUsers || empty($kpiUsers)){
                throw new \Exception("推广员信息不存在");
            }

            $parentIds = [];
            foreach($kpiUsers as $kpiUser){
                $parentIds[] = $kpiUser->user_id;
            }

            $kpiUser = $kpiUsers[0];

            $kpiLinkGoods = new KpiLinkGoods([
                "mall_id"         => $kpiUser->mall_id,
                "inviter_user_id" => $kpiUser->user_id,
                "user_id_list"    => implode(",", $parentIds),
                "created_at"      => time(),
                "mobile"          => !empty($data['mobile']) ? $data['mobile'] : "none",
                "goods_id"        => $data['goods_id'],
                "goods_type"      => $data['goods_type'],
                "date"            => date("Ymd"),
                'store_id'        => $shopData['ss_store_id'],
                'merchant_id'     => $shopData['merchant_id'],
                "point"           => 0
            ]);

            if(!$kpiLinkGoods->save()){
                throw new \Exception(json_encode($kpiLinkGoods->getErrors()));
            }

            //处理奖励
            KpiSetting::setLinkGoodsAward($kpiLinkGoods, $kpiUser, time());

        }catch (\Exception $e){
            $this->error = implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]);
            return false;
        }

        return true;
    }

    /**
     * @deprecated
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
     * 领取优惠券统计
     * @deprecated
     * @param $data
     *  [
     *    'store_id'       => '门店ID',
     *    'inviter_mobile' => '邀请人手机号',
     *    'mobile'         => '访问者手机号',
     *    'coupon_id'      => '优惠券ID'
     *  ]
     * @return boolean
     */
    public function takeCoupon($data){

        $smartShop = new SmartShop();
        $shopData = $smartShop->getStoreDetail($data['store_id']);
        if(!$shopData){
            $this->error = "无法获取门店信息";
            return false;
        }

        try {
            $this->newOrder("store_usercoupons", $shopData['ss_store_id'], $shopData['merchant_id'], $data['coupon_id'], $data['mobile'], $data['inviter_mobile']);
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
     * @param $order_type 订单类型（cyorder|czorder|store_usercoupons）
     * @param $order_id 订单ID
     * @param $mobile 支付手机号
     * @param $inviter_mobile 邀请人手机号
     * @throws \Exception
     * @return boolean
     */
    public function newOrder($order_type, $store_id, $merchant_id, $order_id, $mobile, $inviter_mobile){

        try {
            //获取邀请者本地用户
            $sourceUser = User::findOne(["mobile" => $inviter_mobile]);
            if(!$sourceUser){
                throw new \Exception("用户信息不存在");
            }

            //获取推广员信息
            $kpiUsers = static::getKpiUserAllParents($sourceUser, $store_id, $merchant_id);
            if(!$kpiUsers || empty($kpiUsers)){
                throw new \Exception("推广员信息不存在");
            }

            $parentIds = [];
            foreach($kpiUsers as $kpiUser){
                $parentIds[] = $kpiUser->user_id;
            }

            $kpiUser = $kpiUsers[0];

            $exists = KpiNewOrder::find()->where([
                "mall_id"      => $sourceUser->mall_id,
                "store_id"     => $store_id,
                "merchant_id"  => $merchant_id,
                "source_table" => $order_type,
                "source_id"    => $order_id,
            ])->exists();
            if(!$exists){

                $kpiNewOrder = new KpiNewOrder([
                    "mall_id"         => $kpiUser->mall_id,
                    "inviter_user_id" => $kpiUser->user_id,
                    "user_id_list"    => implode(",", $parentIds),
                    "created_at"      => time(),
                    "mobile"          => !empty($mobile) ? $mobile : "none",
                    "store_id"        => $store_id,
                    "merchant_id"     => $merchant_id,
                    "source_table"    => $order_type,
                    "source_id"       => $order_id,
                    "point"           => 0
                ]);

                if(!$kpiNewOrder->save()){
                    throw new \Exception(json_encode($kpiNewOrder->getErrors()));
                }

                //处理奖励
                KpiSetting::setNewOrderAward($kpiNewOrder, $kpiUser, time());

            }
        }catch (\Exception $e){
            $this->error = implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]);
            return false;
        }

        return true;
    }

    /**
     * 获取奖励设置
     * @deprecated
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
     * @deprecated
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
     * @deprecated
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

    /**
     * 获取所有上级且包括自己的推广员
     * @param User $user
     * @param $store_id
     * @param $merchant_id
     * @return array
     * @throws \Exception
     */
    public static function getKpiUserAllParents(User $user, $store_id, $merchant_id){
        $kpiUsers = [];
        try {
            //获取上级推广员
            $userRelLink = UserRelationshipLink::findOne(["user_id" => $user->id]);
            if(!$userRelLink){
                throw new \Exception("用户【ID:".$user->id."】关系链异常");
            }
            //通过关系链得到上级用户ID、手机号
            $rows = UserRelationshipLink::find()->alias("url")
                ->innerJoin(["u" => User::tableName()], "u.id=url.user_id")
                ->innerJoin(["ku" => KpiUser::tableName()], "ku.mobile=u.mobile AND ku.is_delete=0 AND ku.ss_mch_id='{$merchant_id}' AND ku.ss_store_id='{$store_id}'")
                ->andWhere([
                    "AND",
                    ['<=', 'url.left', $userRelLink->left],
                    ['>=', 'url.right', $userRelLink->right],
                ])->asArray()->select(["url.user_id", "u.mobile"])->orderBy("url.left DESC")->all();
            if($rows){
                foreach($rows as $row){
                    $kpiUser = KpiUser::findOne([
                        "mall_id"      => $user->mall_id,
                        "ss_mch_id"    => $merchant_id,
                        "ss_store_id"  => $store_id,
                        "mobile"       => $row['mobile'],
                        "is_delete"    => 0
                    ]);
                    if($kpiUser){
                        $kpiUsers[] = $kpiUser;
                    }
                }
            }
        }catch (\Exception $e){
            throw $e;
        }
        return $kpiUsers;
    }
}