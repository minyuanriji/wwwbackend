<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 过滤order表字段
 * Author: xuyaoxiang
 * Date: 2020/9/7
 * Time: 14:42
 */

namespace app\plugins\group_buy\filters;

class Order
{
    static public function filterItem($item)
    {
        return [
            'id'              => $item['id'],
            'status'          => $item['status'],
            'order_no'        => $item['order_no'],
            'total_pay_price' => $item['total_pay_price'],
            'is_pay'          => $item['is_pay'],
            'is_send'         => $item['is_send'],
            'cancel_status'   => $item['cancel_status'],
        ];
    }
}