<?php

namespace app\plugins\group_buy\services;

use app\models\Goods;
use app\models\GoodsAttr;
use app\models\OrderDetail;
use app\plugins\group_buy\services\ReturnData;
use yii\db\Exception;
use app\plugins\group_buy\models\PluginGroupBuyGoodsAttr;
use app\plugins\group_buy\models\PluginGroupBuyGoods;

/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 拼团规格
 * Author: xuyaoxiang
 * Date: 2020/9/26
 * Time: 11:54
 */
class GroupBuyGoodsAttrServices
{
    use ReturnData;

    /**
     * @param $num //数量
     * @param $type //add sub
     * @param null $goodsAttrId 规格id
     * @return array
     */
    public function updateStock($num, $type, $goodsAttrId = null)
    {
        if ($goodsAttrId) {
            $goodsAttr = PluginGroupBuyGoodsAttr::find()->where(['attr_id' => $goodsAttrId])->with('attr')->one();
            if (!$goodsAttr) {
                return $this->returnApiResultData(99, '错误的$goodsAttrId');
            }
        }

        // 商品总库存也需要减掉
        /** @var Goods $goods */
        $goods = PluginGroupBuyGoods::find()->where(['goods_id' => $goodsAttr->attr->goods_id, 'deleted_at' => 0])->one();

        if (!$goods) {
            return $this->returnApiResultData(98, '库存操作：商品ID(' . $goodsAttr->attr->goods_id . ')不存在');
        }

        if ($type === 'add') {
            $goodsAttr->stock   += $num;
            $goods->goods_stock += $num;
        } elseif ($type === 'sub') {
            if ($num > $goodsAttr->stock) {
                return $this->returnApiResultData(97, '库存不足');
            }
            $goodsAttr->stock   -= $num;
            $goods->goods_stock -= $num;
        } else {
            return $this->returnApiResultData(96, '错误$type');
        }

        if (!$goodsAttr->save()) {
            return $this->returnApiResultData(95, $goodsAttr->errors[0]);
        }

        if (!$goods->save()) {
            return $this->returnApiResultData(94, $goodsAttr->errors[0]);
        }
        return $this->returnApiResultData(0, "更新库存成功");
    }

    public function goodsAddStock($order)
    {
        /* @var OrderDetail[] $orderDetail */
        $orderDetail     = $order->detail;
        $goodsAttrIdList = [];
        $goodsNum        = [];
        foreach ($orderDetail as $item) {
            $goodsInfo                                = \Yii::$app->serializer->decode($item->goods_info);
            $goodsAttrIdList[]                        = $goodsInfo['goods_attr']['id'];
            $goodsNum[$goodsInfo['goods_attr']['id']] = $item->num;
        }

        $goodsAttrList = PluginGroupBuyGoodsAttr::find()->where(['attr_id' => $goodsAttrIdList])->all();

        foreach ($goodsAttrList as $goodsAttr) {
            $this->updateStock($goodsNum[$goodsAttr->attr_id], 'add', $goodsAttr->attr_id);
        }

        return true;
    }

    /**
     * @param $orderRefund
     * @return bool
     */
    public function goodsAddStockForOrderRefund($orderRefund)
    {
        /* @var OrderDetail $orderDetail */
        /* @var OrderDetail $orderDetail */
        $orderDetail = $orderRefund->detail;

        if ($orderDetail->sign != 'group_buy') {
            return false;
        }

        $goodsInfo = \Yii::$app->serializer->decode($orderDetail->goods_info);

        $this->updateStock($orderDetail->num, 'add', $goodsInfo['goods_attr']['id']);

        return true;
    }
}