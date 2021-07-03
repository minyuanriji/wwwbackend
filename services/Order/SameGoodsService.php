<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/9/11
 * Time: 19:38
 */

namespace app\services\Order;

use app\helpers\ArrayHelper;

class SameGoodsService
{
    private $item;
    private $type;
    private $form_data;
    private $same_goods_list = [];

    public function __construct($item, $type = 1)
    {
        $this->item = $item;
        $this->type = $type;
    }

    public function setFormData($form_data)
    {
        $this->form_data = $form_data;
    }

    public function getSameGoods()
    {
        $this->setSameGoods();
        $this->isMultiAttrGoods();
        //拼接区分规格商品
        $this->setGoodsList();
        $this->item['same_goods_list'] = $this->same_goods_list;
        return $this->item;
    }

    private function setSameGoods()
    {
        foreach ($this->item['goods_list'] as $key => $goodsItem) {
            $goods_id                                     = $goodsItem['id'];
            $this->same_goods_list[$goods_id]['goods_id'] = $goodsItem['id'];

            //同一商品原价总价
            if (isset($this->same_goods_list[$goods_id]['total_original_price'])) {
                $this->same_goods_list[$goods_id]['total_original_price'] += $goodsItem['total_original_price'];
            } else {
                $this->same_goods_list[$goods_id]['total_original_price'] = $goodsItem['total_original_price'];
            }

            //同一商品总价
            if (isset($this->same_goods_list[$goods_id]['total_price'])) {
                $this->same_goods_list[$goods_id]['total_price'] += $goodsItem['total_price'];
            } else {
                $this->same_goods_list[$goodsItem['id']]['total_price'] = $goodsItem['total_price'];
            }

            //同一商品数量
            if (isset($this->same_goods_list[$goods_id]['num'])) {
                $this->same_goods_list[$goods_id]['num'] += $goodsItem['num'];
            } else {
                $this->same_goods_list[$goods_id]['num'] = $goodsItem['num'];
            }
            //规格单价,有会员价，以会员价为准
            $attr_price = isset($goodsItem['goods_attr']['member_price']) ? $goodsItem['goods_attr']['member_price'] : $goodsItem['goods_attr']['original_price'];

            //找出最高规格单价
            if (isset($this->same_goods_list[$goods_id]['max_goods_attr_price'])) {
                if ($this->same_goods_list[$goods_id]['max_goods_attr_price'] < $attr_price) {
                    $this->same_goods_list[$goods_id]['max_goods_attr_price'] = $attr_price;
                    $this->same_goods_list[$goods_id]['max_goods_attr_id']    = $goodsItem['goods_attr']['id'];
                }
            } else {
                $this->same_goods_list[$goods_id]['max_goods_attr_price'] = $attr_price;
                $this->same_goods_list[$goods_id]['max_goods_attr_id']    = $goodsItem['goods_attr']['id'];
            }

            //满额减免,满额金额，可减金额
            $this->same_goods_list[$goods_id]['full_relief_price'] = $goodsItem['full_relief_price'];
            $this->same_goods_list[$goods_id]['fulfil_price']      = $goodsItem['fulfil_price'];

            //商品规格
            $this->same_goods_list[$goods_id]['goods_attr_ids'][] = $goodsItem['goods_attr']['id'];

            //积分减免
            //积分减免类型
            $this->same_goods_list[$goods_id]['forehead_score_type'] = $goodsItem['forehead_score_type'];
            //积分固定值
            $this->same_goods_list[$goods_id]['forehead_score'] = $goodsItem['forehead_score'];
            //是否多件累计
            $this->same_goods_list[$goods_id]['accumulative'] = $goodsItem['accumulative'];
            //可用优惠卷
            $this->same_goods_list[$goods_id]['usable_user_coupon_id'] = 0;
            $this->same_goods_list[$goods_id]['coupon'] = [
                'enabled'         => true,
                'use'             => false,
                'coupon_discount' => price_format(0),
                'user_coupon_id'  => 0,
                'coupon_error'    => "",
            ];

            $this->same_goods_list[$goods_id]['coupon_list'] = [];

            //红包券
            $this->same_goods_list[$goods_id]['max_deduct_integral'] = $goodsItem['max_deduct_integral'];
            $this->same_goods_list[$goods_id]['integral_fee_rate'] = $goodsItem['integral_fee_rate'];
        }
    }


    public function setGoodsList()
    {
        /*foreach ($this->item['same_goods_list'] as $key => $value) {
            unset($this->item['same_goods_list'][$key]['goods_list']);
        }*/

        foreach ($this->item['goods_list'] as $key => $goodsItem) {
            $goods_id = $goodsItem['id'];
            if (!isset($this->same_goods_list[$goods_id]['goods_list'])) {
                $this->same_goods_list[$goods_id]['goods_list'] = array();
            }

            //计算商品总价比例
            $goodsItem['total_price_percent'] = $this->countAttrGoodsPricePercent($goodsItem['total_price'], $this->same_goods_list[$goods_id]['total_price']);

            array_push($this->same_goods_list[$goods_id]['goods_list'], $goodsItem);
        }

        return $this->item;
    }

    /**
     * 计算单个规格商品占同一商品总价的比例;
     * @param $atrr_goods_total_price
     * @param $same_goods_total_price
     */
    public function countAttrGoodsPricePercent($atrr_goods_total_price, $same_goods_total_price)
    {

        if ($same_goods_total_price == 0) {
            return 0;
        }
        return price_format($atrr_goods_total_price / $same_goods_total_price);

    }

    private function isMultiAttrGoods()
    {
        foreach ($this->same_goods_list as $key => $value) {
            if (count($value['goods_attr_ids']) > 1) {
                $this->same_goods_list[$key]['is_multi_attr_goods'] = 1;
            } else {
                $this->same_goods_list[$key]['is_multi_attr_goods'] = 0;
            }
        }
    }

    /**
     * 单一规格商品优惠价格计算，按商品总价占比，计算优惠价格;
     * @param $attr_goods_total_price
     * @param $total_price_percent
     * @param $same_goods_discount_price
     * @return float|string
     */
    static public function countAttrGoodsList($attr_goods_total_price, $total_price_percent, $same_goods_discount_price)
    {

        $price = $attr_goods_total_price - ($total_price_percent * $same_goods_discount_price);
      
        if ($price < 0) {
            return 0;
        }
        return price_format($price);
    }

    public function toArray($same_goods_list)
    {
        foreach ($same_goods_list as $value) {
            $array[] = $value;
        }
        return $array;
    }
}