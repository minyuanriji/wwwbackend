<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/9/14
 * Time: 9:41
 */

namespace app\services\Order;

use app\models\User;

class IntegralService
{
    public $item;
    private $enable_integral;
    private $user_integral; //用户购物券
    private $user_use_integral = 0; //用户已使用购物券
    private $user_remaining_integral; //用户剩余购物券
    private $integral_status = 1; //后台购物券开启状态
    private $integral_price = 1; //多少购物券抵扣一元;购物券比例
    private $use_integral; //用户是否使用购物券
    private $type; //订单预览还是订单提交
    private $total_goods_price;//订单的商品总价

    /**
     * IntegralService constructor.
     * @param $item
     * @param $user_integral //用户可用购物券
     * @param $type
     * @param $use_integral //用多少购物券
     * @param false $enable_integral
     */
    public function __construct($item, $user_integral,$type, $use_integral, $enable_integral = false)
    {
        $this->item              = $item;
        $this->total_goods_price = $item['total_goods_price'];
        $this->type              = $type;
        $this->use_integral      = $use_integral;
        $this->enable_integral   = $enable_integral;
        $this->getUserintegral($user_integral);
        $this->setForeheadintegral();
        $this->getItemintegral();
    }

    /**
     * 用户剩余购物券
     * @return int
     */
    private function getUserRemainingintegral()
    {
        $this->user_remaining_integral = $this->user_integral - $this->user_use_integral;

        return $this->user_remaining_integral;
    }

    //计算实际购物券抵扣最大值
    /*
     * 单件以规格单价高的为准
     * 累计的以商品总价为准
     * 不超过订单总价
     */
    private function setForeheadintegral()
    {
        foreach ($this->item['same_goods_list'] as $key => $value) {
            $this->item['same_goods_list'][$key]['use_num']         = 0;
            $this->item['same_goods_list'][$key]['deduction_price'] = 0;

            if (!isset($value["max_deduct_integral"]) || $value['max_deduct_integral'] == 0) {
                continue;
            }
            $value                                                      = $this->getAccumulative($value);
            $this->item['same_goods_list'][$key]['max_deduct_integral'] = min($value['accumulative_max_deduct_integral'], $value['total_price']);
        }
    }

    private function getAccumulative($value)
    {
        $value['accumulative_max_deduct_integral'] = $value['num'] * $value['max_deduct_integral'];

        return $value;
    }

    private function getIntegral($price)
    {
        if ($this->integral_price > 0) {
            return price_format($price / $this->integral_price);
        }
        return 0;
    }

    public function countIntegral()
    {
        if (!$this->use_integral) {
            return $this->item;
        }

        if (!$this->integral_status) {
            return $this->item;
        }

        foreach ($this->item['same_goods_list'] as $key => $value) {
            //当用户剩余购物券等于,跳过
            if (($this->getUserRemainingintegral() == 0) or $this->total_goods_price == 0) {
                continue;
            }
            $value['max_deduct_integral']                       = min($value['max_deduct_integral'], $this->user_remaining_integral, $this->item['total_goods_price'], $value['total_price']);
            $value['max_deduct_integral']                       = intval($value['max_deduct_integral']);
            $this->item['same_goods_list'][$key]['total_price'] -= $value['max_deduct_integral'];
            $this->user_use_integral                            += $this->getIntegral($value['max_deduct_integral']);
            $this->user_remaining_integral                      -= $this->getIntegral($value['max_deduct_integral']);

            //当前商品可抵扣购物券，和可抵扣金额
            $this->item['same_goods_list'][$key]['deduction_price'] = $value['max_deduct_integral'];
            $this->item['same_goods_list'][$key]['use_num']         = $this->getIntegral($value['max_deduct_integral']);

            $this->total_goods_price -= $value['max_deduct_integral'];

            //计算优惠比例
            foreach ($value['goods_list'] as $goods_list_key => $goods_item) {
                $goods                = &$this->item['same_goods_list'][$key]['goods_list'][$goods_list_key];
                $goods['total_price'] = SameGoodsService::countAttrGoodsList($goods_item['total_price'], $goods_item['total_price_percent'], $value['max_deduct_integral']);
                //抵扣购物券价格
                $goods['integral_price'] = price_format($goods_item['total_price_percent'] * $value['max_deduct_integral']);
                //抵扣购物券数量
                $goods['use_integral_price'] = intval($goods_item['total_price_percent'] * $this->getIntegral($value['max_deduct_integral']));
                //是否使用购物券
                $goods['use_integral'] = $goods['use_integral_price'] > 0 ? 1 : 0;
            }
        }

        $this->getItemintegral();

        $this->item['total_goods_price'] = $this->total_goods_price;

        return $this->item;
    }

    public function getUserintegral($user_integral)
    {
        $this->user_integral           = $user_integral;
        //$this->user_integral           = User::getCanUseIntegral($user_id);
        $this->user_remaining_integral = $this->user_integral;
    }


    public function getItemintegral()
    {
        return $this->item['integral'] = [
            'use'                      => $this->use_integral,
            'use_num'                  => intval($this->user_use_integral),
            //购物券抵扣总金额
            'integral_deduction_price' => intval($this->user_use_integral),
            'can_use'                  => $this->user_use_integral > 0 ? true : false,
            'user_integral'            => $this->user_integral,
            //剩余购物券金额
            'user_remaining_integral'  => $this->user_remaining_integral,
        ];
    }
}