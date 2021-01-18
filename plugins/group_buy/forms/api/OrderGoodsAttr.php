<?php

namespace app\plugins\group_buy\forms\api;

use app\forms\api\order\OrderGoodsAttr as ParentOrderGoodsAttr;
use app\models\GoodsAttr;
use app\plugins\group_buy\models\PluginGroupBuyGoodsAttr;
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/9/15
 * Time: 20:13
 */
class OrderGoodsAttr extends ParentOrderGoodsAttr
{
    /**
     * @param $goodsAttrId
     * @throws \Exception
     */
    public function setGoodsAttrById($goodsAttrId)
    {
        /* @var GoodsAttr $goodsAttr */
        $goodsAttr = GoodsAttr::find()->where(['id' => $goodsAttrId, "is_delete" => 0])->one();
        $group_buy_goods_attr = PluginGroupBuyGoodsAttr::find()->where(['attr_id' => $goodsAttr->id])->one();
        $goodsAttr->price = $group_buy_goods_attr->group_buy_price;
        if (!$goodsAttr) {
            throw new \Exception('无法查询到规格信息。');
        }

        $this->setGoodsAttr($goodsAttr);
    }
}