<?php
namespace app\mch\forms\order;

class CheckoutOrderAutoSettleForm extends MchAutoSettleForm {

    public $order_id;

    public function rules(){
        return array_merge(parent::rules(), [
            [['order_id'], 'required'],
            [['order_id'], 'integer']
        ]);
    }

    /**
     * 商家结账单自动结算
     * @return boolean
     */
    public function save(){
        $this->desc = "结账单（".$this->order_id."）结算";
        return parent::save();
    }

}