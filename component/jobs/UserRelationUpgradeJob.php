<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-16
 * Time: 15:48
 */

namespace app\component\jobs;


use app\helpers\SerializeHelper;
use app\models\CommonOrder;
use app\models\CommonOrderDetail;
use app\models\Goods;
use app\models\GoodsCatRelation;
use app\models\Order;
use app\models\RelationSetting;
use app\models\User;
use yii\base\BaseObject;
use yii\db\Exception;
use yii\queue\JobInterface;
use yii\queue\Queue;

/**
 * Class UserRelationUpgradeJob
 * @package app\component\jobs
 * @Notes 该队列判断用户是否升级为推客更新用户 is_inviter字段
 */
class UserRelationUpgradeJob extends BaseObject implements JobInterface
{

    public $user_id;
    public $mall_id;
    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        \Yii::warning("---UserRelationUpgradeJob start---");
        // TODO: Implement execute() method.
        try {
            \Yii::warning("用户{$this->user_id}绑定关系的资格开始执行");
            $user = User::findOne($this->user_id);
            if ($user && $user->is_inviter) {
                \Yii::warning("用户{$this->user_id}已经有绑定关系的资格");
                return;
            }



            if ($user && !$user->is_inviter) {
                $relation = RelationSetting::findOne(['mall_id' => $this->mall_id, 'use_relation' => 1, 'is_delete' => 0]);
                if ($relation) {
//                    if ($relation->buy_compute_way == RelationSetting::BUY_COMPUTE_WAY_PAY_AFTER) {
//                        $order_status = CommonOrder::STATUS_IS_PAY;
//                    }
//                    if ($relation->buy_compute_way == RelationSetting::BUY_COMPUTE_WAY_FINISH_AFTER) {
//                        $order_status = CommonOrder::STATUS_IS_COMPLETE;
//                    }
                    $order_status = $relation->buy_compute_way;
                    /********************************************************************
                     * 无条件
                     * @update_author zal
                     * @update_time 2020-06-09 17:18
                     ********************************************************************
                     */
                    if ($relation->get_power_way == RelationSetting::GET_POWER_WAY_NO_CONDITION) {
                        $user->setInviter();
                    }
                    /********************************************************************
                     * 任意条件
                     ********************************************************************
                     */


                    if ($relation->get_power_way == RelationSetting::GET_POWER_WAY_OR) {

                        \Yii::warning('========================='.'购物---------------');
                        /**
                         * 任何商品
                         */
                        if ($relation->buy_goods_selected) {

                            if ($relation->buy_goods_way == RelationSetting::BUY_GOODS_WAY_ANY_GOODS) {  //任意商品



                                if ($order_status == CommonOrder::STATUS_IS_PAY) {
                                    $order = CommonOrder::find()->where(['user_id' => $this->user_id])->andWhere(['is_pay' => CommonOrder::STATUS_IS_PAY])->exists();
                                } else {
                                    $order = CommonOrder::find()->where(['status' => CommonOrder::STATUS_COMPLETE, 'user_id' => $this->user_id])->exists();
                                }
                                if ($order) {
                                    $user->setInviter();
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
                                                ->andWhere(['co.is_pay' =>  $order_status])
                                                ->andWhere(['cod.user_id' => $user->id, 'cod.goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
                                                ->andWhere(['cod.goods_id' => $goods_ids])
                                                ->exists();
                                        }
                                        if ($order_status == 2) {
                                            $order_goods = CommonOrder::find()->alias('co')
                                                ->leftJoin(['cod' => CommonOrderDetail::tableName()], 'cod.common_order_id=co.id')
                                                ->andWhere(['co.status' => CommonOrder::STATUS_IS_COMPLETE])
                                                ->andWhere(['cod.user_id' => $user->id, 'cod.goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
                                                ->andWhere(['cod.goods_id' => $goods_ids])
                                                ->exists();
                                        }
                                        if ($order_goods) {
                                            \Yii::warning($relation->goods_ids);
                                            $user->setInviter();

                                            \Yii::warning("用户{$this->user_id}绑定关系的资格执行完毕");
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
                                                ->andWhere(['g.is_delete' => 0, 'g.is_recycle' => 0])
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
                                                    ->andWhere(['co.is_pay' => $order_status])
                                                    ->andWhere(['cod.user_id' => $user->id, 'cod.goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
                                                    ->asArray()
                                                    ->all();
                                            }

                                            if ($order_status == 2) {
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
                                                $user->setInviter();
                                                \Yii::warning("用户{$this->user_id}绑定关系的资格执行完毕");
                                                return;
                                            } else {

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
                            $order_num=0;
                            if($order_status==1){
                                $order_num = CommonOrder::find()->where(['user_id' => $user->id])->andWhere(['or',['status'=>CommonOrder::STATUS_IS_PAY],['is_pay' => CommonOrder::STATUS_IS_PAY]])->count();
                            }
                            if($order_status==2){
                                $order_num = CommonOrder::find()->where(['user_id' => $user->id])->andWhere(['status'=>CommonOrder::STATUS_COMPLETE])->count();
                            }
                            \Yii::warning('=========================消费次数---------------'.$order_num);
                            if ($order_num >= $relation->buy_num) {
                                $user->setInviter();
                                \Yii::warning("用户{$this->user_id}绑定关系的资格执行完毕");
                                return;
                            }
                        }

                        /**
                         * 消费金额
                         */
                        if ($relation->buy_price_selected && $relation->buy_price) {
                            if ($order_status == CommonOrder::STATUS_IS_PAY) {
                                $order_price = CommonOrder::find()->where(['user_id' => $user->id, 'is_pay' => CommonOrder::STATUS_IS_PAY])->sum('pay_price');
                            } else {
                                $order_price = CommonOrder::find()->where(['user_id' => $user->id, 'status' => CommonOrder::STATUS_COMPLETE])->sum('pay_price');
                            }
                            \Yii::warning('=========================消费金额 OR---------------'.$order_price);
                            if ($order_price >= $relation->buy_price) {
                                $user->setInviter();
                                return;
                            }
                        }
                    }


                    /********************************************************************
                     * 必须全部条件都要满足
                     ********************************************************************
                     */
                    if ($relation->get_power_way = RelationSetting::GET_POWER_WAY_AND) {

                        \Yii::warning("与条件");
                        $isFinish = false; //任务是否完成
                        if ($relation->buy_goods_selected) {
                            if ($relation->buy_goods_way == RelationSetting::BUY_GOODS_WAY_ANY_GOODS) {  //任意商品
                                if ($order_status == CommonOrder::STATUS_IS_PAY) {
                                    $order = CommonOrder::find()->where(['user_id' => $this->user_id])->andWhere(['is_pay' => CommonOrder::STATUS_IS_PAY])->exists();
                                } else {
                                    $order = CommonOrder::find()->where(['status' => CommonOrder::STATUS_COMPLETE, 'user_id' => $this->user_id])->exists();
                                }
                                if ($order) {
                                    $isFinish = true;
                                } else {
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
                                                ->andWhere(['co.is_pay' => $order_status])
                                                ->andWhere(['cod.user_id' => $user->id, 'cod.goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
                                                ->andWhere(['cod.goods_id' => $goods_ids])
                                                ->exists();
                                        }
                                        if ($order_status == 2) {
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
                                            ->andWhere(['g.is_delete' => 0, 'g.is_recycle' => 0])
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
                                                ->andWhere(['co.is_pay' => CommonOrder::STATUS_IS_PAY])
                                                ->andWhere(['cod.user_id' => $user->id, 'cod.goods_type' => CommonOrderDetail::TYPE_MALL_GOODS])
                                                ->asArray()
                                                ->all();
                                        }

                                        if ($order_status == 2) {
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
                            if ($order_status == CommonOrder::STATUS_IS_PAY) {
                                $order_num = CommonOrder::find()->where(['user_id' => $user->id, 'is_pay' => CommonOrder::STATUS_IS_PAY])->count();
                            }else{
                                $order_num = CommonOrder::find()->where(['user_id' => $user->id, 'status' => CommonOrder::STATUS_COMPLETE])->count();
                            }
                            \Yii::warning('=========================购买次数 AND---------------'.$order_num);
                            if ($order_num >= $relation->buy_num) {
                                $isFinish = true;
                            } else {
                                return;
                            }
                        }
                        /**
                         * 消费金额
                         */
                        if ($relation->buy_price_selected && $relation->buy_price) {
                            if ($order_status == CommonOrder::STATUS_IS_PAY) {
                                $order_price = CommonOrder::find()->where(['user_id' => $user->id, 'is_pay' => CommonOrder::STATUS_IS_PAY])->sum('pay_price');
                            }else{
                                $order_price = CommonOrder::find()->where(['user_id' => $user->id, 'status' => CommonOrder::STATUS_COMPLETE])->sum('pay_price');
                            }
                            \Yii::warning('=========================消费金额 AND---------------'.$order_price);
                            if ($order_price >= $relation->buy_price) {
                                $isFinish = true;
                            } else {
                                return;
                            }
                        }
                        if ($isFinish) {
                            $user->setInviter();
                            return;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            \Yii::error("UserRelationUpgradeJob error "."File:".$e->getFile().";Line:".$e->getLine().";Message:".$e->getMessage());
            \Yii::debug($e->getMessage());
        }
    }
}