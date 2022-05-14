<?php

namespace app\plugins\smart_shop\components\wechat_msg_template;

use app\notification\wechat_template_message\WechatTemplateMessageNew;

/***
 * 订单付款通知门店
 */
class CyorderPaidMsgTemplate extends WechatTemplateMessageNew {

    public $title;
    public $order_sn;
    public $goods_name;
    public $pay_price;
    public $nickname;
    public $mobile;
    public $remark;

    /**
     * 数据内容
     * @return array
     */
    protected function data() {
        return [
            "first" => $this->title,
            "keyword1" => $this->order_sn,
            "keyword2" => $this->goods_name,
            "keyword3" => $this->pay_price,
            "keyword4" => $this->nickname,
            "keyword5" => $this->mobile,
            "remark"   => $this->remark
        ];
    }

    /**
     * 模板ID
     * @return string
     */
    protected function templateId(){
        return "zRzF4qdG4AkeaBTL8bp2WAq2atw2838obKzsmTXIwKw";
    }
}