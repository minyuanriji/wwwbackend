<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/9/17
 * Time: 19:43
 */

namespace app\services\Order;

use app\helpers\ArrayHelper;

class AttrGoodsService
{
    private $item;
    private $type;

    public function __construct($item, $type = 1)
    {
        $this->item = $item;
        $this->type = $type;
    }

    public function getGoodsList()
    {
        $this->setNecKey();
        $this->setGoodListValue();
        $this->sortGoodsList();
        $this->pushGoodsList();
        return $this->item;
    }

    public function setNecKey()
    {
        foreach ($this->item['same_goods_list'] as $key => $value) {
            foreach ($value['goods_list'] as $k => $goods) {
                //方便引用
                $temp                          = &$this->item['same_goods_list'][$key]['goods_list'][$k];

                if (!isset($temp['coupon'])) {
                    $temp['coupon'] = [
                        'enabled'         => true,
                        'use'             => false,
                        'coupon_discount' => price_format(0),
                        'user_coupon_id'  => 0,
                        'coupon_error'    => "",
                    ];
                }

                //积分key
                //实际积分抵扣价格
                if (!isset($temp['score_price'])) {
                    $temp['score_price'] = 0;
                }

                //红包券
                if (!isset($temp['integral_price'])) {
                    $temp['integral_price'] = 0;
                }

                //使用了多少积分
                if (!isset($temp['use_score_price'])) {
                    $temp['use_score_price'] = 0;
                }

                //是否使用积分
                if (!isset($temp['use_score'])) {
                    $temp['use_score'] = 0;
                }

                //满减key,实际减免金额
                if (!isset($temp['actual_full_relief_price'])) {
                    $temp['actual_full_relief_price'] = 0;
                }
            }
        }
    }

    public function setGoodListValue()
    {
        foreach ($this->item['same_goods_list'] as &$value) {
            foreach ($value['goods_list'] as &$goods) {
                //优惠券key
                if (isset($goods['coupon_discount_price'])) {
                    $goods['coupon']['coupon_discount'] = $goods['coupon_discount_price'];
                }

                if ($value['usable_user_coupon_id'] > 0) {
                    $goods['coupon']['use']            = true;
                    $goods['coupon']['user_coupon_id'] = $value['usable_user_coupon_id'];
                }
            }
        }
    }

    /**
     * same_goods_list转换成goods_list，方便入库
     * @return mixed
     */
    public function pushGoodsList()
    {
        $this->item['goods_list'] = array();
        foreach ($this->item['same_goods_list'] as $key => $value) {
            foreach ($value['goods_list'] as $goods) {
                array_push($this->item['goods_list'], $goods);
            }
        }

        return $this->item;
    }

    /**
     * same_goods_list中的优惠券数据转换成goods_list的优惠券数据,优惠卷信息以单规格商品总价最高的记录入库,排第一的为总价最高的规格总价
     */
    /*poster function getUsableUserCouponId()
    {
        foreach ($this->item['same_goods_list'] as $key => $value) {
            foreach ($value['goods_list'] as $k => $goods) {
                if ($k == 0) {
                    $this->item['same_goods_list'][$key]['goods_list'][$k]['usable_user_coupon_id'] = $this->item['same_goods_list'][$goods['id']]['usable_user_coupon_id'];
                    $this->item['same_goods_list'][$key]['goods_list'][$k]['coupon']                = $this->item['same_goods_list'][$goods['id']]['coupon'];
                }
            }
        }
    }*/

    /**
     * 排第一的为总价最高的规格总价
     * @return mixed
     */
    public function sortGoodsList()
    {
        foreach ($this->item['same_goods_list'] as &$value) {
            $value['goods_list'] = $this->arraySort($value['goods_list'], 'total_price');
        }

        return $this->item;
    }

    /**
     * @param $array
     * @param $keys
     * @param int $sort
     * @return mixed
     */
    function arraySort($array, $keys, $sort = SORT_DESC)
    {
        $keysValue = [];
        foreach ($array as $k => $v) {
            $keysValue[$k] = $v[$keys];
        }
        array_multisort($keysValue, $sort, $array);
        return $array;
    }
}