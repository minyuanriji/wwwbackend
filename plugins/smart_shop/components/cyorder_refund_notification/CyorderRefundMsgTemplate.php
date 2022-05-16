<?php

namespace app\plugins\smart_shop\components\cyorder_refund_notification;

use app\notification\wechat_template_message\WechatTemplateMessageNew;

class CyorderRefundMsgTemplate extends WechatTemplateMessageNew {

    public $title;
    public $date;
    public $nickname;
    public $mobile;
    public $refund_money;
    public $refund_reason;
    public $remark;


    /**
     * 数据内容
     * @return array
     */
    protected function data() {
        return [
            "first"    => $this->title,
            "keyword1" => $this->date,
            "keyword2" => $this->nickname,
            "keyword3" => $this->mobile,
            "keyword4" => $this->refund_money,
            "keyword5" => $this->refund_reason,
            "remark"   => $this->remark
        ];
    }

    /**
     * 模板ID
     * @return string
     */
    protected function templateId(){
        return "0-KvnKOo8nVqjLhDiyY_GMxIqSoHhvglif0PUIM1QQ4";
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