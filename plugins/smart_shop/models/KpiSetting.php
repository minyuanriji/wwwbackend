<?php

namespace app\plugins\smart_shop\models;

use app\models\BaseActiveRecord;
use app\plugins\smart_shop\components\SmartShop;

class KpiSetting extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_smartshop_kpi_setting}}';
    }

    public function rules(){
        return [
            [['mall_id', 'ss_mch_id', 'ss_store_id', 'created_at', 'updated_at', 'type', 'source_table', 'source_id', 'value'], 'required'],
            [['is_delete'], 'safe']
        ];
    }

    /**
     * 设置注册奖励
     * @param KpiRegister $kpiRegister
     * @param KpiUser $kpiUser
     * @param $timestamp
     * @return void
     */
    public static function setRegisterAward(KpiRegister $kpiRegister, KpiUser $kpiUser, $timestamp){
        $startTime = strtotime(date("Y-m-d") . " 00:00:00", $timestamp);
        $query = KpiRegister::find()->andWhere([
            "AND",
            ["mall_id" => $kpiUser->mall_id],
            "inviter_user_id" => $kpiUser->user_id,
            'store_id' => $kpiRegister->store_id,
            'merchant_id' => $kpiRegister->merchant_id,
            [">", "created_at", $startTime],
            ["<", "created_at", ($startTime + 3600 * 24 - 1)]
        ]);
        $dayTotalPoint = (int)$query->sum("point");
        $dayUserNum = (int)$query->count() + 1;

        //获得配置
        $kpiSetting = static::getKpiSetting($kpiUser, "register", "common", "0");

        $awardData['award_point']     = 0;
        $awardData['match_rule']      = '';
        $awardData['rule_data']       = '';
        $awardData['day_total_point'] = $dayTotalPoint;
        $awardData['day_user_num']    = $dayUserNum;

        if($kpiSetting){
            $ruleData = !empty($kpiSetting->value) ? json_decode($kpiSetting->value, true) : [];
            $matchRule = static::matchRule(isset($ruleData['rule_list']) ? $ruleData['rule_list'] : [], "order_num", $dayUserNum);
            if($matchRule){
                $awardData['award_point'] = $matchRule['award_point'];
                if(isset($ruleData['enable_day_limit']) && $ruleData['enable_day_limit']){
                    $awardData['award_point'] = min($awardData['award_point'], max(0, $ruleData['day_limit_point'] - $dayTotalPoint));
                }
            }
            $awardData['match_rule'] = $matchRule ? $matchRule : '';
            $awardData['rule_data']  = $ruleData;
        }

        $kpiRegister->point      = $awardData['award_point'];
        $kpiRegister->award_data = json_encode($awardData, JSON_UNESCAPED_UNICODE);
        $kpiRegister->save();
    }

    /**
     * 设置分享奖励
     * @param KpiLinkGoods $linkGoods
     * @param KpiUser $kpiUser
     * @param $timestamp
     * @return void
     */
    public static function setLinkGoodsAward(KpiLinkGoods $linkGoods, KpiUser $kpiUser, $timestamp){
        $startTime = strtotime(date("Y-m-d") . " 00:00:00", $timestamp);
        $query = KpiLinkGoods::find()->andWhere([
            "AND",
            ["mall_id" => $kpiUser->mall_id],
            "inviter_user_id" => $kpiUser->user_id,
            'store_id' => $linkGoods->store_id,
            'merchant_id' => $linkGoods->merchant_id,
            [">", "created_at", $startTime],
            ["<", "created_at", ($startTime + 3600 * 24 - 1)]
        ]);
        $dayTotalPoint = (int)$query->sum("point");
        $dayShareNum = (int)$query->count() + 1;

        //获得配置
        $cond['type']         = "new_order";
        $cond['source_table'] = -1;
        $cond['source_id']    = $linkGoods->goods_id;
        if($linkGoods->goods_type == "goods") { //商品
            $cond['source_table'] = 'goods';
        }elseif($linkGoods->goods_type == "giftpacks") { //套餐
            $cond['source_table'] = 'giftpack';
        }elseif($linkGoods->goods_type == "coupon"){ //优惠券
            $cond['source_table'] = 'coupon';
        }
        $kpiSetting = static::getKpiSetting($kpiUser, "share_link", $cond['source_table'], $cond['source_id']);

        $awardData['award_point']     = 0;
        $awardData['match_rule']      = '';
        $awardData['rule_data']       = '';
        $awardData['day_total_point'] = $dayTotalPoint;
        $awardData['day_share_num']   = $dayShareNum;

        if($kpiSetting){
            $ruleData = !empty($kpiSetting->value) ? json_decode($kpiSetting->value, true) : [];
            $matchRule = static::matchRule(isset($ruleData['rule_list']) ? $ruleData['rule_list'] : [], "order_num", $dayShareNum);
            if($matchRule){
                $awardData['award_point'] = $matchRule['award_point'];
                if(isset($ruleData['enable_day_limit']) && $ruleData['enable_day_limit']){
                    $awardData['award_point'] = min($awardData['award_point'], max(0, $ruleData['day_limit_point'] - $dayTotalPoint));
                }
            }
            $awardData['match_rule'] = $matchRule ? $matchRule : '';
            $awardData['rule_data']  = $ruleData;
        }

        $linkGoods->point      = $awardData['award_point'];
        $linkGoods->award_data = json_encode($awardData, JSON_UNESCAPED_UNICODE);
        $linkGoods->save();
    }

    /**
     * 设置新订单KPI奖励
     * @param KpiNewOrder $newOrder
     * @param KpiUser $kpiUser
     * @param $timestamp
     * @return void
     */
    public static function setNewOrderAward(KpiNewOrder $newOrder, KpiUser $kpiUser, $timestamp){
        $startTime = strtotime(date("Y-m-d") . " 00:00:00", $timestamp);
        $query = KpiNewOrder::find()->andWhere([
            "AND",
            ["mall_id" => $kpiUser->mall_id],
            "inviter_user_id" => $kpiUser->user_id,
            'store_id' => $newOrder->store_id,
            'merchant_id' => $newOrder->merchant_id,
            [">", "created_at", $startTime],
            ["<", "created_at", ($startTime + 3600 * 24 - 1)]
        ]);
        $dayTotalPoint = (int)$query->sum("point"); //当日总积分
        $dayOrderNum = (int)$query->count() + 1; //当日订单数量

        //获得配置
        $cond['type']         = "new_order";
        $cond['source_table'] = -1;
        $cond['source_id']    = -1;
        $smartShop = new SmartShop();
        if($newOrder->source_table == "cyorder"){ //商品
            $cond['source_table'] = 'goods';
            $rows = $smartShop->getCyorderDetailByOrderId($newOrder->source_id);
            if($rows && count($rows) == 1){
                $cond['source_id'] = $rows[0]['goods_id'];
            }
        }elseif($newOrder->source_table == "giftpack_order"){ //套餐
            $cond['source_table'] = 'giftpack';
            $row = $smartShop->getGiftpackOrderDetail($newOrder->source_id);
            if(!empty($row)){
                $cond['source_id'] = $row['giftpack_id'];
            }
        }elseif($newOrder->source_table == "store_usercoupons"){ //优惠券
            $cond['source_table'] = 'coupon';
            $row = $smartShop->getStoreUsercouponsDetail($newOrder->source_id);
            if(!empty($row)){
                $cond['source_id'] = $row['coupon_id'];
            }
        }

        $kpiSetting = static::getKpiSetting($kpiUser, "new_order", $cond['source_table'], $cond['source_id']);

        $awardData['award_point']     = 0;
        $awardData['match_rule']      = '';
        $awardData['rule_data']       = '';
        $awardData['day_total_point'] = $dayTotalPoint;
        $awardData['day_order_num']   = $dayOrderNum;

        if($kpiSetting){
            $ruleData = !empty($kpiSetting->value) ? json_decode($kpiSetting->value, true) : [];
            $matchRule = static::matchRule(isset($ruleData['rule_list']) ? $ruleData['rule_list'] : [], "order_num", $dayOrderNum);
            if($matchRule){
                $awardData['award_point'] = $matchRule['award_point'];
                if(isset($ruleData['enable_day_limit']) && $ruleData['enable_day_limit']){
                    $awardData['award_point'] = min($awardData['award_point'], max(0, $ruleData['day_limit_point'] - $dayTotalPoint));
                }
            }
            $awardData['match_rule'] = $matchRule ? $matchRule : '';
            $awardData['rule_data']  = $ruleData;
        }

        $newOrder->point      = $awardData['award_point'];
        $newOrder->award_data = json_encode($awardData, JSON_UNESCAPED_UNICODE);
        $newOrder->save();
    }

    /**
     * 获取KPI设置
     * @param KpiUser $kpiUser
     * @param $type
     * @param $sourceTable
     * @param $sourceId
     * @return KpiSetting|null
     */
    public static function getKpiSetting(KpiUser $kpiUser, $type, $sourceTable, $sourceId){
        $cond['type']         = $type;
        $cond['source_table'] = $sourceTable;
        $cond['source_id']    = $sourceId;

        $commonWhere = [
            "mall_id"      => $kpiUser->mall_id,
            "ss_mch_id"    => $kpiUser->ss_mch_id,
            "ss_store_id"  => $kpiUser->ss_store_id,
            "is_delete"    => 0,
        ];
        $kpiSetting = KpiSetting::findOne(array_merge($commonWhere, $cond));
        if(!$kpiSetting){
            //查询通用规则
            $cond['source_table'] = 'common';
            $cond['source_id']    = 0;
            $kpiSetting = KpiSetting::findOne(array_merge($commonWhere, $cond));
        }
        return $kpiSetting;
    }

    /**
     * 匹配规则
     * @param $rules
     * @param $key
     * @param $num
     * @return array
     */
    private static function matchRule($rules, $key, $num){
        static::sortRules($rules, $key);
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
    private static function sortRules(&$rules, $key){
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