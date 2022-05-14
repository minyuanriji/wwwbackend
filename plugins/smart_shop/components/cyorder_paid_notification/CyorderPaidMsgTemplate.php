<?php
/*
 * @link:http://www.@copyright: Copyright (c) @Author: Mr.Lin
 * @Email: 746027209@qq.com
 * @Date: 2021-07-06 14:13
 */

namespace app\plugins\smart_shop\components\cyorder_paid_notification;

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

    /**
     * 跳转小程序
     * @return string
     */
    protected function miniprogram(){
        return [
            "appid"    => "wx25a6c376389beca2",
            "pagepath" => "/"
        ];
    }
}