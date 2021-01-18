<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-27
 * Time: 14:39
 */

namespace app\controllers\api;

use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\models\CommonOrder;
use app\models\CommonOrderDetail;
use app\models\DistrictData;
use app\models\Goods;
use app\models\GoodsCatRelation;
use app\models\GoodsWarehouse;
use app\models\RelationSetting;
use app\models\User;

/**
 * Class DefaultController
 * @package app\controllers\api
 * @Notes 默认控制器
 */
class DefaultController extends ApiController
{
    public function actionIndex()
    {
        $commonOrder = CommonOrder::findOne(1);
        $commonOrder->status=3;
        $commonOrder->save();
        $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => 'success',]);
    }


    public function doUserUpgrade($user_id = 1)
    {

        //以下代码放入队列中
        //传入用户id

        $user = User::findOne($user_id);
        if ($user && !$user->is_inviter) {
            $relation = RelationSetting::findOne(['mall_id' => 4, 'use_relation' => 1, 'is_delete' => 0]);
            if ($relation) {
                if ($relation->buy_compute_way == RelationSetting::BUY_COMPUTE_WAY_PAY_AFTER) {
                    $order_status = CommonOrder::STATUS_IS_PAY;
                }
                if ($relation->buy_compute_way == RelationSetting::BUY_COMPUTE_WAY_FINISH_AFTER) {
                    $order_status = CommonOrder::STATUS_IS_COMPLETE;
                }
                /********************************************************************
                 * 任意条件
                 ********************************************************************
                 */
                if ($relation->get_power_way == RelationSetting::GET_POWER_WAY_OR) {
                    /**
                     * 任何商品
                     */
                    if ($relation->buy_goods_selected) {
                        if ($relation->buy_goods_way == RelationSetting::BUY_GOODS_WAY_ANY_GOODS) {  //任意商品
                            $order = CommonOrder::find()->where(['status' => $order_status, 'user_id' => $user_id])->exists();
                            if ($order) {
                                $user->is_inviter = 1;
                                $user->inviter_at = time();
                                $user->save();
                                return;
                            }
                        }
                        /**
                         * 指定商品
                         */
                        if ($relation->buy_goods_way == RelationSetting::BUY_GOODS_WAY_SELECTED_GOODS) {//通过指定商品
                            if ($relation->goods_ids) {
                                $goods_ids = (array)SerializeHelper::decode($relation->goods_ids);
                                if (count($goods_ids)) {
                                    $order_goods = null;
                                    if ($order_status == CommonOrder::STATUS_IS_PAY) {
                                        $order_goods = CommonOrder::find()->alias('co')
                                            ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'cod.common_order_id=co.id')
                                            ->andWhere(['co.status' => [CommonOrder::STATUS_IS_COMPLETE, $order_status]])
                                            ->andWhere(['cod.user_id' => $user->id, 'cod.goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
                                            ->andWhere(['cod.goods_id' => $goods_ids])
                                            ->exists();
                                    }
                                    if ($order_status == CommonOrder::STATUS_IS_COMPLETE) {
                                        $order_goods = CommonOrder::find()->alias('co')
                                            ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'cod.common_order_id=co.id')
                                            ->andWhere(['co.status' => CommonOrder::STATUS_IS_COMPLETE])
                                            ->andWhere(['cod.user_id' => $user->id, 'cod.goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
                                            ->andWhere(['cod.goods_id' => $goods_ids])
                                            ->exists();
                                    }
                                    if ($order_goods) {
                                        \Yii::warning($relation->goods_ids);
                                        $user->is_inviter = 1;
                                        $user->inviter_at = time();
                                        $user->save();
                                        return;
                                    }
                                }
                            }
                        }
                        /**
                         * 指定分类
                         */
                        if ($relation->buy_goods_way == RelationSetting::BUY_GOODS_WAY_SELECTED_CAT) {//指定分类
                            if ($relation->cat_ids) {
                                $cat_ids = (array)(SerializeHelper::decode($relation->cat_ids));
                                if (count($cat_ids)) {
                                    $cat_ids = (array)SerializeHelper::decode($relation->cat_ids);
                                    if (count($cat_ids)) {
                                        //首先获取这些分类下面有哪些商品
                                        $catGoodsIds = [];
                                        $cat_goods_list = Goods::find()->alias('g')
                                            ->andWhere(['g.is_delete' => 0])
                                            ->leftJoin(['gcr' => GoodsCatRelation::tableName()], 'gcr.goods_warehouse_id=g.goods_warehouse_id')
                                            ->andWhere(['gcr.cat_id' => $cat_ids])
                                            ->select('g.id')
                                            ->asArray()
                                            ->all();
                                        foreach ($cat_goods_list as $item) {
                                            $catGoodsIds[] = $item['id'];
                                        }

                                        if ($order_status == CommonOrder::STATUS_IS_PAY) {
                                            $order_goods_list = CommonOrder::find()->alias('co')
                                                ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'cod.common_order_id=co.id')
                                                ->select('cod.goods_id')
                                                ->andWhere(['co.status' => [CommonOrder::STATUS_IS_COMPLETE, $order_status]])
                                                ->andWhere(['cod.user_id' => $user->id, 'cod.goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
                                                ->asArray()
                                                ->all();
                                        }

                                        if ($order_status == CommonOrder::STATUS_IS_COMPLETE) {
                                            $order_goods_list = CommonOrder::find()->alias('co')
                                                ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'cod.common_order_id=co.id')
                                                ->select('cod.goods_id')
                                                ->andWhere(['co.status' => CommonOrder::STATUS_IS_COMPLETE])
                                                ->andWhere(['cod.user_id' => $user->id, 'cod.goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
                                                ->asArray()
                                                ->all();
                                        }


                                        $buyGoodsIds = [];
                                        foreach ($order_goods_list as $item) {
                                            $buyGoodsIds[] = $item['goods_id'];
                                        }
                                        $intersection = array_intersect($buyGoodsIds, $catGoodsIds);
                                        if (count($intersection)) {
                                            $user->is_inviter = 1;
                                            $user->inviter_at = time();
                                            $user->save();
                                            return;
                                        } else {
                                            dd('不可升级');
                                            return;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    /**
                     * 消费次数
                     */
                    if ($relation->buy_num_selected && $relation->buy_num) {
                        $order_num = CommonOrder::find()->where(['user_id' => $user->id, 'status' => $order_status])->count();
                        if ($order_num >= $relation->buy_num) {
                            $user->is_inviter = 1;
                            $user->inviter_at = time();
                            $user->save();
                            return;
                        }
                    }

                    /**
                     * 消费金额
                     */
                    if ($relation->buy_price_selected && $relation->buy_price) {
                        $order_price = CommonOrder::find()->where(['user_id' => $user->id, 'status' => $order_status])->sum('pay_price');
                        if ($order_price >= $relation->buy_price) {
                            $user->is_inviter = 1;
                            $user->inviter_at = time();
                            $user->save();
                            return;
                        }
                    }
                }


                /********************************************************************
                 * 必须全部条件都要满足
                 ********************************************************************
                 */
                if ($relation->get_power_way = RelationSetting::GET_POWER_WAY_AND) {
                    $isFinish = false; //任务是否完成
                    if ($relation->buy_goods_selected) {
                        if ($relation->buy_goods_way == RelationSetting::BUY_GOODS_WAY_ANY_GOODS) {  //任意商品
                            $order = CommonOrder::find()->where(['status' => $order_status, 'user_id' => $user_id])->exists();
                            if ($order) {
                                $isFinish = true;
                            } else {
                                dd('不可升级');
                                return;
                            }
                        }
                        /**
                         * 指定商品
                         */
                        if ($relation->buy_goods_way == RelationSetting::BUY_GOODS_WAY_SELECTED_GOODS) {//通过指定商品
                            $isFinish = false;
                            if ($relation->goods_ids) {
                                $goods_ids = (array)SerializeHelper::decode($relation->goods_ids);
                                if (count($goods_ids)) {
                                    if ($order_status == CommonOrder::STATUS_IS_PAY) {
                                        $order_goods = CommonOrder::find()->alias('co')
                                            ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'cod.common_order_id=co.id')
                                            ->andWhere(['co.status' => [CommonOrder::STATUS_IS_COMPLETE, $order_status]])
                                            ->andWhere(['cod.user_id' => $user->id, 'cod.goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
                                            ->andWhere(['cod.goods_id' => $goods_ids])
                                            ->exists();
                                    }
                                    if ($order_status == CommonOrder::STATUS_IS_COMPLETE) {
                                        $order_goods = CommonOrder::find()->alias('co')
                                            ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'cod.common_order_id=co.id')
                                            ->andWhere(['co.status' => CommonOrder::STATUS_IS_COMPLETE])
                                            ->andWhere(['cod.user_id' => $user->id, 'cod.goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
                                            ->andWhere(['cod.goods_id' => $goods_ids])
                                            ->exists();
                                    }
                                    if ($order_goods) {
                                        $isFinish = true;
                                    } else {

                                        return;
                                    }
                                }
                            }
                        }
                        /**
                         * 指定分类
                         */
                        if ($relation->buy_goods_way == RelationSetting::BUY_GOODS_WAY_SELECTED_CAT) {//指定分类
                            if ($relation->cat_ids) {
                                $cat_ids = (array)SerializeHelper::decode($relation->cat_ids);
                                if (count($cat_ids)) {
                                    //首先获取这些分类下面有哪些商品
                                    $catGoodsIds = [];
                                    $cat_goods_list = Goods::find()->alias('g')
                                        ->andWhere(['g.is_delete' => 0])
                                        ->leftJoin(['gcr' => GoodsCatRelation::tableName()], 'gcr.goods_warehouse_id=g.goods_warehouse_id')
                                        ->andWhere(['gcr.cat_id' => $cat_ids])
                                        ->select('g.id')
                                        ->asArray()
                                        ->all();
                                    foreach ($cat_goods_list as $item) {
                                        $catGoodsIds[] = $item['id'];
                                    }

                                    if ($order_status == CommonOrder::STATUS_IS_PAY) {
                                        $order_goods_list = CommonOrder::find()->alias('co')
                                            ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'cod.common_order_id=co.id')
                                            ->select('cod.goods_id')
                                            ->andWhere(['co.status' => [CommonOrder::STATUS_IS_COMPLETE, $order_status]])
                                            ->andWhere(['cod.user_id' => $user->id, 'cod.goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
                                            ->asArray()
                                            ->all();
                                    }

                                    if ($order_status == CommonOrder::STATUS_IS_COMPLETE) {
                                        $order_goods_list = CommonOrder::find()->alias('co')
                                            ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'cod.common_order_id=co.id')
                                            ->select('cod.goods_id')
                                            ->andWhere(['co.status' => CommonOrder::STATUS_IS_COMPLETE])
                                            ->andWhere(['cod.user_id' => $user->id, 'cod.goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
                                            ->asArray()
                                            ->all();
                                    }


                                    $buyGoodsIds = [];
                                    foreach ($order_goods_list as $item) {
                                        $buyGoodsIds[] = $item['goods_id'];
                                    }
                                    $intersection = array_intersect($buyGoodsIds, $catGoodsIds);
                                    if (count($intersection)) {
                                        $isFinish = true;
                                    } else {
                                        dd('不可升级');
                                        return;
                                    }
                                }
                            }
                        }
                    }
                    /**
                     * 购买次数
                     */
                    if ($relation->buy_num_selected && $relation->buy_num) {
                        $order_num = CommonOrder::find()->where(['user_id' => $user->id, 'status' => $order_status])->count();
                        if ($order_num >= $relation->buy_num) {
                            $isFinish = true;
                        } else {
                            dd('不可升级');
                            return;
                        }
                    }
                    /**
                     * 消费金额
                     */
                    if ($relation->buy_price_selected && $relation->buy_price) {
                        $order_price = CommonOrder::find()->where(['user_id' => $user->id, 'status' => $order_status])->sum('pay_price');
                        if ($order_price >= $relation->buy_price) {
                            $isFinish = true;
                        } else {
                            dd('不可升级');
                            return;
                        }
                    }
                    if ($isFinish) {
                        dd('满足条件可以升级');
                        $user->is_inviter = 1;
                        $user->inviter_at = time();
                        $user->save();
                        return;
                    }
                }
            }
        }


    }


    public function actionSetting()
    {


    }

}