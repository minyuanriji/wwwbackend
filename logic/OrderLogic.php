<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单相关逻辑处理类
 * Author: zal
 * Date: 2020-05-05
 * Time: 16:36
 */

namespace app\logic;


use app\component\caches\OrderCache;

class OrderLogic
{

    /**
     * 获取支付方式配置数据
     * @return array
     */
    public static function getPaymentTypeConfig(){
        $paymentConfigs = AppConfigLogic::getPaymentConfig();
        $payTypeData = [];
        if(!empty($paymentConfigs)){
            if(isset($paymentConfigs["wechat_status"]) && $paymentConfigs["wechat_status"] == 1){
                $payTypeData[] = "wechat";
            }
            if(isset($paymentConfigs["balance_status"]) && $paymentConfigs["balance_status"] == 1){
                $payTypeData[] = "balance";
            }
        }
        if(empty($payTypeData)){
            $payTypeData[] = "wechat";
        }
        $payTypeData[] = "alipay";
        return $payTypeData;
    }

    /**
     * 设置提醒发货间隔数,24小时内只能提醒一次
     * @param $id
     * @param $value
     */
    public static function setRemindSendCache($id,$value){
        $key = "_remind_send_".$id;
        self::getOrderCacheClass()->duration = 3600*24;
        self::getOrderCacheClass()->addOrderCache($key,$value);
    }

    /**
     * 获取提醒发货缓存
     * @param $id
     * @return bool
     */
    public static function getRemindSendCache($id){
        $key = "_remind_send_".$id;
        return self::getOrderCacheClass()->getOrderCache($key);
    }

    /**
     * 获取配置缓存类
     * @return OrderCache
     */
    private static function getOrderCacheClass(){
        return new OrderCache();
    }
}