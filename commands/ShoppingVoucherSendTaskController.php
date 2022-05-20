<?php


namespace app\commands;

class ShoppingVoucherSendTaskController extends BaseCommandController {

    public function actions(){
        return [
            'ssco' => 'app\commands\shopping_voucher_send_task\SmartshopCyorderSendAction',
            'sso'  => 'app\commands\shopping_voucher_send_task\SmartshopOrderSendAction',

            'mco'  => 'app\commands\shopping_voucher_send_task\MchCheckoutOrderSendAction',
            'ho'   => 'app\commands\shopping_voucher_send_task\HotelOrderSendAction',
            'hf'   => 'app\commands\shopping_voucher_send_task\AddcreditOrderSendAction',
            'gf'   => 'app\commands\shopping_voucher_send_task\GiftpacksOrderSendAction',
            'od'   => 'app\commands\shopping_voucher_send_task\OrderDetailSendAction',
            'oo'   => 'app\commands\shopping_voucher_send_task\OilOrderSendAction',
            'oe'   => 'app\commands\shopping_voucher_send_task\OrderExpressSendAction',
        ];
    }
}