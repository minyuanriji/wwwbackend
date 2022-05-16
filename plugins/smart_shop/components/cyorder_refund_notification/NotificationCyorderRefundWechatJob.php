<?php

namespace app\plugins\smart_shop\components\cyorder_refund_notification;

use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\helpers\NotificationHelper;
use yii\base\Component;
use yii\queue\JobInterface;

class NotificationCyorderRefundWechatJob extends Component implements JobInterface{

    public $mall_id;
    public $order_id;

    public function execute($queue){
        try {

            $smartShop = new SmartShop();
            $detail = $smartShop->getCyorderDetail($this->order_id);
            if(!$detail['is_pay']){
                throw new \Exception("订单未付款");
            }

            $store = $smartShop->getStoreDetail($detail['store_id']);
            if(!$store){
                throw new \Exception("无法获取门店信息");
            }

            $setting = NotificationHelper::getWechatTemplate($this->mall_id, $store['merchant_id'], $store['ss_store_id']);
            if($setting && $setting['status'] && $setting['enable']){
                $res = (new CyorderRefundMsgTemplate([
                    "mall_id"       => $this->mall_id,
                    "title"         => "你有一条新的退款消息",
                    "date"          => date("Y-m-d H:i:s", $detail['apply_time']),
                    "nickname"      => !empty($detail['nickname']) ? $detail['nickname'] : "普通用户",
                    "mobile"        => $detail['u_mobile'],
                    "refund_money"  => $detail['total_price'],
                    "refund_reason" => $detail['refund_reason'],
                    "remark"        => "请登陆后台查看详情"
                ]))->send($setting['data']['openid']);
            }
        }catch (\Exception $e){
            echo "NotificationCyorderRefundWechatJob::execute\n";
            echo "error:" . $e->getMessage() . "\n";
            echo "file:" . $e->getFile() . "\n";
            echo "line:" . $e->getLine();
        }
    }
}