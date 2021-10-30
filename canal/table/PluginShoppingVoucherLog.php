<?php

namespace app\canal\table;

use app\notification\StorePayVoucherNotification;
use app\notification\AddcreditRechargeNotification;
use app\notification\VoucherConsumptionNotification;

class PluginShoppingVoucherLog
{
    //from--收入  target--支出
    //1、购物卷订单支付--target_order  2、订单取消--from_order_cancel  3、管理员操作--admin  4、订单退款--from_order_refund
    //5、门店扫码--from_mch_checkout_order   6、1688订单支付--target_alibaba_distribution_order
    //7、1688订单退款-1688_distribution_order_detail_refund  8、酒店订单支付--from_hotel_order  9、话费订单--from_addcredit_order
    //10、大礼包订单--from_giftpacks_order    11、商品订单获得购物券--from_order_detail
    const VOUCHER_TYPE = [1, 5, 9];

    public function insert($rows)
    {
        foreach ($rows as $row)
        {
            if (isset($row['source_type'])) {
                if (in_array($row['source_type'], self::VOUCHER_TYPE)) {
                    if ($row['type'] == 1) {
                        switch ($row['source_type'])
                        {
                            case 5:
                                $row['source_type'] = 'from_mch_checkout_order';
                                StorePayVoucherNotification::send($row);
                                break;
                            case 9:
                                break;
                                $row['source_type'] = 'from_addcredit_order';
                                AddcreditRechargeNotification::send($row);
                            default;
                        }
                    } else {
                        switch ($row['source_type'])
                        {
                            case 1:
                                $row['source_type'] = 'target_order';
                                VoucherConsumptionNotification::send($row);
                                break;
                            default;
                        }
                    }
                    \Yii::error('IncomeLogNotice:' . json_encode($row) . '---time:' . date("Y-m-d H:i:s", time()));
                }
            }
        }
    }

    public function update($mixDatas)
    {
    }
}
