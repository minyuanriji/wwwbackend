<?php

namespace app\plugins\smart_shop\components\jobs;

use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\components\wechat_msg_template\CyorderPaidMsgTemplate;
use app\plugins\smart_shop\helpers\NotificationHelper;
use yii\base\Component;
use yii\queue\JobInterface;

class NotificationCyorderPaidWechatJob extends Component implements JobInterface{

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

            $setting = NotificationHelper::getWechatTemplate($this->mall_id, $store['merchant_id'], $store['ss_store_id']);
            if($setting && $setting['status'] && $setting['enable']){
                $res = (new CyorderPaidMsgTemplate([
                    "mall_id"    => $this->mall_id,
                    "title"      => "您有一笔新的订单！",
                    "order_sn"   => $detail['order_no'],
                    "goods_name" => $goodsName,
                    "pay_price"  => $detail['total_price'],
                    "nickname"   => !empty($detail['nickname']) ? $detail['nickname'] : "普通用户",
                    "mobile"     => $detail['u_mobile'],
                    "remark"     => "请及时处理！"
                ]))->send($setting['data']['openid']);
            }

        }catch (\Exception $e){
            echo "NotificationCyorderPaidWechatJob::execute\n";
            echo "error:" . $e->getMessage() . "\n";
            echo "file:" . $e->getFile() . "\n";
            echo "line:" . $e->getLine();
        }
    }
}