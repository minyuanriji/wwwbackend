<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-01
 * Time: 21:49
 */

namespace app\forms\common\prints\content;

/**
 * Class Order
 * @package app\forms\common\prints\content
 * @property GoodsContent[] $goods_list
 */
class OrderContent extends BaseContent
{
    public $mall_name;
    public $order_type;
    public $pay_type;
    public $send_type_text;
    public $order_no;
    public $created_at;

    public $is_attr;
    public $goods_list;

    public $total_goods_original_price;
    public $express_price;
    public $coupon_discount_price;
    public $use_score;
    public $score_deduction_price;
    public $member_discount_price;
    public $back_price;
    public $total_pay_price;

    public $send_type;
    public $name;
    public $mobile;
    public $address;
    public $store_name;
    public $store_mobile;
    public $store_address;
    public $remark;
    public $order_form = [];
    public $plugin_data = [];
}
