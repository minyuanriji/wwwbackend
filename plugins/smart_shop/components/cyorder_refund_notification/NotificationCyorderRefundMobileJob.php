<?php

namespace app\plugins\smart_shop\components\cyorder_refund_notification;

use app\helpers\sms\Sms;
use app\models\Mall;
use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\helpers\NotificationHelper;
use yii\base\Component;
use yii\queue\JobInterface;

class NotificationCyorderRefundMobileJob extends Component implements JobInterface{

    public $mall_id;
    public $order_id;

    public function execute($queue){
        try {

            \Yii::$app->setMall(Mall::findOne($this->mall_id));

            $smartShop = new SmartShop();
            $detail = $smartShop->getCyorderDetail($this->order_id);
            if(!$detail['is_pay']){
                throw new \Exception("订单未付款");
            }

            $store = $smartShop->getStoreDetail($detail['store_id']);
            if(!$store){
                throw new \Exception("无法获取门店信息");
            }

            $setting = NotificationHelper::getMobile($this->mall_id, $store['merchant_id'], $store['ss_store_id']);
            if($setting && $setting['status'] && $setting['enable']){
                $sms = new Sms();
                $res = $sms->sendOrderRefundMessage([$setting['data']['mobile']], $detail['order_no']);
            }

        }catch (\Exception $e){
            echo "NotificationCyorderRefundMobileJob::execute\n";
            echo "error:" . $e->getMessage() . "\n";
            echo "file:" . $e->getFile() . "\n";
            echo "line:" . $e->getLine();
        }
    }

}