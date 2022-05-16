<?php

namespace app\plugins\smart_shop\components\czorder_paid_notification;

use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\helpers\NotificationHelper;
use yii\base\Component;
use yii\queue\JobInterface;

class NotificationCzorderPaidWechatJob extends Component implements JobInterface{

    public $mall_id;
    public $order_id;

    public function execute($queue){
        try {

            $smartShop = new SmartShop();
            $detail = $smartShop->getCzorderDetail($this->order_id);
            if($detail['state'] != 2){
                throw new \Exception("订单未付款");
            }

            $store = $smartShop->getStoreDetail($detail['store_id']);
            if(!$store){
                throw new \Exception("无法获取门店信息");
            }

            $goodsName = "会员充值";

            $setting = NotificationHelper::getWechatTemplate($this->mall_id, $store['merchant_id'], $store['ss_store_id']);
            if($setting && $setting['status'] && $setting['enable']){
                $res = (new CzorderPaidMsgTemplate([
                    "mall_id"        => $this->mall_id,
                    "title"          => "手机号“".$detail['u_mobile']."”会员充值了一笔订单！",
                    "store_name"     => $detail['store_name'],
                    "user_type"      => "-",
                    "recharge_money" => $detail['total_price'],
                    "remain_money"   => $detail['balance'],
                    "date"           => date("Y-m-d H:i:s", $detail['create_time']),
                    "remark"         => "订单号：" . $detail['order_no']
                ]))->send($setting['data']['openid']);
            }
        }catch (\Exception $e){
            echo "NotificationCzorderPaidWechatJob::execute\n";
            echo "error:" . $e->getMessage() . "\n";
            echo "file:" . $e->getFile() . "\n";
            echo "line:" . $e->getLine();
        }
    }
}