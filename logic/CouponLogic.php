<?php

namespace app\logic;

use app\models\Coupon;
use app\models\CouponCatRelation;
use app\models\CouponGoodsRelation;
use app\models\Goods;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\UserCoupon;

class CouponLogic
{
    public static function getCouponCatRelations($coupon_id)
    {
        $couponCatRelations = CouponCatRelation::findAll([
            'coupon_id' => $coupon_id,
            'is_delete' => 0,
        ]);

        $catIdList = [];
        foreach ($couponCatRelations as $couponCatRelation) {
            $catIdList[] = $couponCatRelation->cat_id;
        }

        return $catIdList;
    }

    /**
     * 相关分类的商品详情id
     * @param $catIdList
     * @return array
     */
    public static function getGoodsCatRelations($catIdList)
    {
        $goodsCatRelations = GoodsCatRelation::find()
            ->select('gcr.goods_warehouse_id')
            ->alias('gcr')
            ->leftJoin(['gc' => GoodsCats::tableName()], 'gcr.cat_id=gc.id')
            ->where(['gc.is_delete' => 0, 'gcr.cat_id' => $catIdList, 'gcr.is_delete' => 0])
            ->all();
        $couponGoodsIdList = [];
        foreach ($goodsCatRelations as $goodsCatRelation) {
            $couponGoodsIdList[] = $goodsCatRelation->goods_warehouse_id;
        }

        return $couponGoodsIdList;
    }

    public static function checkUserCouponTime($userCouponId)
    {
        $nowDateTime = time();
        /** @var UserCoupon $userCoupon */
        $userCoupon = UserCoupon::find()->where([
            'AND',
            ['id' => $userCouponId],
            ['user_id' => \Yii::$app->user->identity->getId()],
            ['is_delete' => 0],
            ['is_use' => 0],
            [
                '<=',
                'begin_at',
                $nowDateTime
            ],
            [
                '>=',
                'end_at',
                $nowDateTime
            ],
        ])->one();

        if (!$userCoupon) {
            return false;
        }

        return true;
    }

    /**
     * 获取用户可用的优惠券
     * @param $userCoupon
     * @param $coupon
     * @param $val
     * @return array
     */
    public static function getUserUsableCoupon($userCoupon,$coupon,$val){
        if ($coupon["appoint_type"] == Coupon::APPOINT_TYPE_CAT || $coupon["appoint_type"] == Coupon::APPOINT_TYPE_GOODS) {
            if ($coupon["appoint_type"] == Coupon::APPOINT_TYPE_CAT) { // 指定分类可用
                /**
                 * 获取相关分类
                 */
                $catIdList = self::getCouponCatRelations($coupon['id']);

                /** @var GoodsCatRelation[] $goodsCatRelations */
                $couponGoodsIdList = self::getGoodsCatRelations($catIdList);

            } else { // 指定商品可用
                $couponGoodsRelations = CouponGoodsRelation::findAll([
                    'coupon_id' => $coupon["id"],
                    'is_delete' => 0,
                ]);
                $couponGoodsIdList = [];
                foreach ($couponGoodsRelations as $couponGoodsRelation) {
                    $couponGoodsIdList[] = $couponGoodsRelation->goods_warehouse_id;
                }
            }

            $goods = Goods::findOne($val['goods_id']);
            if (!$goods) {
                return [];
            }
            /**
             * 商品是否匹配到优惠卷商品范围
             */
            if ($couponGoodsIdList && !empty($couponGoodsIdList)
                && !in_array($goods['goods_warehouse_id'], $couponGoodsIdList)) {
                return [];
            }
            \Yii::info("2".$userCoupon, "xiang");

            if ($val["total_original_price"] < $userCoupon['coupon_min_price']) { // 可用的商品原总价未达到优惠券使用条件
                $val['coupon']['coupon_error'] = '所选优惠券未满足使用条件.';
                return [];
            }
        } elseif ($coupon["appoint_type"] == 3) { // 全商品通用
            if ($val['total_price'] <= 0) { // 价格已优惠到0不再使用优惠券
                $val['coupon']['coupon_error'] = '商品价格已为0无法使用优惠券';
                return [];
            }
            if ($val['total_original_price'] < $userCoupon["coupon_min_price"]) { // 商品原总价未达到优惠券使用条件

                $val['coupon']['coupon_error'] = '所选优惠券未满足使用条件..';
                return [];
            }
        }

        /**
         * 检查优惠卷时间
         */
        if (!self::checkUserCouponTime($userCoupon['id'])) {
            $val['coupon']['coupon_error'] = '所选优惠券时间范围有误';
            return [];
        }

        return $userCoupon;
    }
}
