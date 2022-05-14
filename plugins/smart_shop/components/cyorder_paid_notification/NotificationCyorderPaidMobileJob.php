<?php

namespace app\plugins\smart_shop\components\cyorder_paid_notification;

use app\logic\AppConfigLogic;
use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\helpers\NotificationHelper;
use Overtrue\EasySms\EasySms;
use yii\base\Component;
use yii\queue\JobInterface;

class NotificationCyorderPaidMobileJob extends Component implements JobInterface{

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

            if($detail['type'] == 3){
                $goodsName = "小程序订单，自助结账";
            }elseif($detail['state'] == 2) {
                $goodsName = "小程序订单，配送订单";
            }elseif($detail == 3){
                $goodsName = "小程序订单，自提订单";
            }else{
                $goodsName = "小程序订单";
            }

            $setting = NotificationHelper::getMobile($this->mall_id, $store['merchant_id'], $store['ss_store_id']);
            if($setting && $setting['status'] && $setting['enable']){
                $this->getSms()->send($setting['data']['mobile'], sprintf("您有一条新的订单，订单号:%s", $detail['order_no']));
            }

        }catch (\Exception $e){
            echo "NotificationCyorderPaidMobileJob::execute\n";
            echo "error:" . $e->getMessage() . "\n";
            echo "file:" . $e->getFile() . "\n";
            echo "line:" . $e->getLine();
        }
    }

    private function getSms(){
        $config = [
            // HTTP 请求的超时时间（秒）
            'timeout' => 5.0,

            // 默认发送配置
            'default' => [
                // 网关调用策略，默认：顺序调用
                'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

                // 默认可用的发送网关
                'gateways' => [
                    'aliyun',
                ],
            ],
            // 可用的网关配置
            'gateways' => [
                'errorlog' => [
                    'file' => '/runtime/easy-sms.log',
                ],
                //...
            ],
        ];

        $smsConfig = AppConfigLogic::getSmsConfig();

        // 阿里云短信配置
        if ($smsConfig['platform'] == 'aliyun') {
            $config['gateways']['aliyun'] = [
                'access_key_id'     => $smsConfig['access_key_id'],
                'access_key_secret' => $smsConfig['access_key_secret'],
                'sign_name'         => $smsConfig['template_name'],
            ];
        }

        return new EasySms($config);
    }
}