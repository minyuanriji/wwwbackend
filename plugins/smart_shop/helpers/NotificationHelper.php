<?php

namespace app\plugins\smart_shop\helpers;

use app\plugins\smart_shop\models\Notifications;

class NotificationHelper{

    /**
     * 获取微信公众号通知配置
     * @return array
     */
    public static function getWechatTemplate($mall_id, $merchant_id, $store_id){
        $data = static::getNotification($mall_id, $merchant_id, $store_id, "wechat_template");
        $data['status'] = isset($data['data']['openid']) && !empty($data['data']['openid']) ? 1 : 0;
        return $data;
    }

    /**
     * 获取短信通通知配置
     * @return array
     */
    public static function getMobile($mall_id, $merchant_id, $store_id){
        $data = static::getNotification($mall_id, $merchant_id, $store_id, "mobile");
        $data['status'] = isset($data['data']['mobile']) && !empty($data['data']['mobile']) ? 1 : 0;
        return $data;
    }

    /**
     * 获取邮件通知配置
     * @return array
     */
    public static function getEmail($mall_id, $merchant_id, $store_id){
        $data = static::getNotification($mall_id, $merchant_id, $store_id, "email");
        $data['status'] = isset($data['data']['email']) && !empty($data['data']['email']) ? 1 : 0;
        return $data;
    }

    /**
     * 获取通知配置
     * @param $mall_id
     * @param $merchant_id
     * @param $store_id
     * @param $type
     * @return array
     */
    public static function getNotification($mall_id, $merchant_id, $store_id, $type){
        $data = ["id" => 0, "type" => $type, "status" => 0, "enable" => 0, "data" => []];
        $notification = Notifications::findOne([
            "mall_id"     => $mall_id,
            "ss_mch_id"   => $merchant_id,
            "ss_store_id" => $store_id,
            "type"        => $type
        ]);
        if($notification){
            $params = !empty($notification->data_json) ? @json_decode($notification->data_json, true) : [];
            $data['id']     = $notification->id;
            $data['data']   = $params;
            $data['enable'] = $notification->enable;
        }
        return $data;
    }
}