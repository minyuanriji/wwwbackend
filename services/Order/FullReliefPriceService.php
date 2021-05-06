<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/9/12
 * Time: 11:36
 */

namespace app\services\Order;

class FullReliefPriceService
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function countFullRelief()
    {
        if (!isset($this->item['same_goods_list'])) {
            return false;
        }
        $this->item['total_full_relief_price'] = 0;
        foreach ($this->item['same_goods_list'] as $key => $value) {
            if ($value['fulfil_price'] > 0) {
                $value                                                           = $this->countFullReliefOne($value);
                $this->item['same_goods_list'][$key]['total_price']              -= $value['actual_full_relief_price'];
                $this->item['same_goods_list'][$key]['actual_full_relief_price'] = $value['actual_full_relief_price'];

                $this->item['total_goods_price']       -= $value['actual_full_relief_price'];
                $this->item['total_full_relief_price'] += $value['actual_full_relief_price'];

                //计算优惠比例
                foreach ($value['goods_list'] as $goods_list_key => $goods_item) {
                    $goods                      = &$this->item['same_goods_list'][$key]['goods_list'][$goods_list_key];
                    $goods['actual_full_relief_price'] = price_format($goods_item['total_price_percent'] * $value['actual_full_relief_price']);
                    $goods['total_price']       = SameGoodsService::countAttrGoodsList($goods_item['total_price'], $goods_item['total_price_percent'], $value['actual_full_relief_price']);
                }
            }
        }

        return $this->item;
    }

    private function countFullReliefOne($value)
    {
        if ($value['total_original_price'] >= $value['fulfil_price']) {
            $value['actual_full_relief_price'] = min($value['total_price'], $value['full_relief_price']);
            $value['total_price']              -= $value['actual_full_relief_price'];
        } else {
            $value['actual_full_relief_price'] = 0;
        }

        return $value;
    }
}