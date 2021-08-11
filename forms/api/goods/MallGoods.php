<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单api-商品
 * Author: zal
 * Date: 2020-05-11
 * Time: 10:50
 */

namespace app\forms\api\goods;

use app\models\BaseModel;
use app\models\OrderDetail;

class MallGoods extends BaseModel
{
    /**
     * 处理订单展示的商品数据
     * @param OrderDetail $orderDetail
     * @return array
     */
    public static function getGoodsData($orderDetail)
    {
        // 暂时先处理下, TODO 应该限制orderDetail类型
        if (is_array($orderDetail)) {
            $orderDetail = (object)$orderDetail;
        }
        if (isset($orderDetail->order) && $orderDetail->order && is_array($orderDetail->order)) {
            $orderDetail->order = (object)$orderDetail->order;
        }

        $goodsInfo = [];
        try {
            $goodsAttrInfo = \Yii::$app->serializer->decode($orderDetail->goods_info);
            $goodsInfo['name'] = isset($goodsAttrInfo['goods_attr']['name']) ? $goodsAttrInfo['goods_attr']['name'] : '';
            $goodsInfo['attr_list'] = isset($goodsAttrInfo['attr_list']) ? $goodsAttrInfo['attr_list'] : [];
            $goodsInfo['pic_url'] = isset($goodsAttrInfo['goods_attr']['pic_url']) && $goodsAttrInfo['goods_attr']['pic_url'] ? $goodsAttrInfo['goods_attr']['pic_url'] : $goodsAttrInfo['goods_attr']['cover_pic'];

            $goodsInfo['num'] = isset($orderDetail->num) ? $orderDetail->num : 0;
            $goodsInfo['total_original_price'] = isset($orderDetail->total_original_price) ? $orderDetail->total_original_price : 0;
            $goodsInfo['member_discount_price'] = isset($orderDetail->member_discount_price) ? $orderDetail->member_discount_price : 0;

            try {
                $sign = $orderDetail->order && $orderDetail->order->sign ? $orderDetail->order->sign : 'webapp';
                if ($orderDetail->order->mch_id > 0) {
                    $sign = 'mch';
                }
                $plugins = \Yii::$app->plugin->getPlugin($sign);
                if (is_callable(array($plugins, 'getGoodsUrl'))) {
                    $goodsInfo['page_url'] = $plugins->getGoodsUrl($orderDetail->goods);
                } else {
                    $goodsInfo['page_url'] = '';
                }

            } catch (\Exception $exception) {
                $goodsInfo['page_url'] = '';
            }

        } catch (\Exception $exception) {
            // dd($exception);
        }
        return $goodsInfo;
    }
}