<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/9/11
 * Time: 14:05
 */

namespace app\plugins\group_buy\filters;

class Goods
{
    public function filterItem($goods_id)
    {
        if (!$goods_id) {
            return [];
        }

        $goods = \app\models\Goods::findOne($goods_id);

        if (!$goods_id) {
            return [];
        }

        return [
            'id'          => $goods->id,
            'goods_stock' => $goods->goods_stock,
            'sign'        => $goods->sign,
            'name'        => $goods->name,
            'detail'      => $goods->detail,
            'cover_pic'   => $goods->coverPic,
            'pic_url'     => $goods->picUrl,
        ];
    }

    public function filterItemForGroupBuy($goods_id)
    {
        if (!$goods_id) {
            return [];
        }

        $goods = \app\models\Goods::findOne($goods_id);

        if (!$goods) {
            return [];
        }

        return [
            'id'             => $goods->id,
            'goods_stock'    => $goods->goods_stock,
            'sign'           => $goods->sign,
            'name'           => $goods->name,
            'detail'         => $goods->detail,
            'cover_pic'      => $goods->coverPic,
            'pic_url'        => $goods->picUrl,
            'original_price' => $goods->OriginalPrice
        ];
    }
}