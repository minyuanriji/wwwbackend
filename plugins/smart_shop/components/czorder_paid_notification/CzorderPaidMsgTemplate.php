<?php

namespace app\plugins\smart_shop\components\czorder_paid_notification;

use app\notification\wechat_template_message\WechatTemplateMessageNew;

/***
 * 订单付款通知门店
 */
class CzorderPaidMsgTemplate extends WechatTemplateMessageNew {

    public $title;
    public $store_name;
    public $user_type;
    public $recharge_money;
    public $remain_money;
    public $date;
    public $remark;

    /**
     * 数据内容
     * @return array
     */
    protected function data() {
        return [
            "first" => $this->title,
            "keyword1" => $this->store_name,
            "keyword2" => $this->user_type,
            "keyword3" => $this->recharge_money,
            "keyword4" => $this->remain_money,
            "keyword5" => $this->date,
            "remark"   => $this->remark
        ];
    }

    /**
     * 模板ID
     * @return string
     */
    protected function templateId(){
        return "vgYS4XP-fPzUdja3odoRnteTnuPJXizba6_b0GUuP-0";
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