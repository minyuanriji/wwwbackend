<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销佣金订单记录公共处理类
 * Author: zal
 * Date: 2020-05-26
 * Time: 10:30
 */

namespace app\plugins\stock\forms\common;

use app\models\BaseModel;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use app\plugins\stock\events\AgentCommissionEvent;
use app\plugins\stock\handlers\AgentCommissionHandler;
use app\plugins\stock\models\Stock;
use app\plugins\stock\models\StockGoods;
use app\plugins\stock\models\StockGoodsDetail;
use app\plugins\stock\models\StockLevel;
use app\plugins\stock\models\StockOrder;
use app\plugins\stock\models\StockSetting;
use app\plugins\mch\models\Goods;

class StockOrderCommon extends BaseModel
{

    /**
     * 添加分销分佣订单
     * @param Order $order
     * @param $level
     * @throws \Exception
     */
    public static function addAgentOrder($order,$level){
        \Yii::warning("----添加分销分佣订单开始----");
        $firstParentUser = $secondParentUser = $thirdParentUser = [];
        try{
            //是否开启分销内购
            $agentSetting = StockSetting::getValueByKey(StockSetting::IS_SELF_BUY);
            //判断下单人自己是否分销商
            $user_id = $order->user_id;
            $user = User::findOne(['id' => $user_id]);
            if (!$user) {
                return;
            }
            \Yii::warning("下单人数据:".var_export($user->attributes,true));
            //第二级推荐人
            $second_user_id = $user->second_parent_id;
            //第三级推荐人
            $third_user_id = $user->third_parent_id;
            $agent = isset($user->stock) ? $user->stock : [];
            //$agent = Stock::find()->where(['mall_id' => $order->mall_id, 'user_id' => $user_id, 'is_delete' => 0])->with("user")->one();
            if ((!empty($agentSetting) && $agentSetting == 1) && !empty($agent)) {
                //开启分销内购，自己是第一级，上级为第二级，上上级为第三级
                $firstParentUser = $agent;
                $second_user_id = $user->parent_id;
                $third_user_id = $user->second_parent_id;
            }else{
                //没有满足分销内购条件，则按级别进行分销
                $firstParentUser = Stock::findOne(['mall_id' => $order->mall_id,'user_id' => $user->parent_id, 'is_delete' => 0]);
            }

            if(empty($firstParentUser)){
                return;
            }

            $secondParentUser = $thirdParentUser = [];
            //分销设置
            $agentSetting = StockSetting::getData($order->mall_id);
            //分销等级
            if($level >= 2){
                if($second_user_id > 0){
                    $secondParentUser = Stock::findOne(['mall_id' => $order->mall_id,'user_id' => $second_user_id, 'is_delete' => 0]);
                }
                if($third_user_id > 0){
                    $thirdParentUser = Stock::findOne(['mall_id' => $order->mall_id,'user_id' => $third_user_id, 'is_delete' => 0]);
                }
            }
            //订单详情
            $orderDetails = $order->getDetail()->with(
                ['goods' => function ($query) {
                    $query->with(['stock']);
                }])->andWhere(['is_delete' => 0])->all();
            $firstPriceTotal = 0;
            $secondPriceTotal = 0;
            $thirdPriceTotal = 0;
            $agentOrderList = [];
            /** @var OrderDetail $orderDetail */
            foreach ($orderDetails as $orderDetail) {
                \Yii::warning("添加分销分佣循环处理开始:".var_export($orderDetail,true));
                $isNewInsert = false;
                $data = [];
                /** @var Goods $goodsAgentSetting */
                $goodsAgentSetting = $orderDetail->goods;
                $first_price = 0;
                $second_price = 0;
                $third_price = 0;
                $before_first_price = $before_second_price = $before_third_price = 0;
                $before_first_parent_id = $before_second_parent_id = $before_third_parent_id = 0;
                $shareType = 0;
                $goodsInfo = $orderDetail->decodeGoodsInfo($orderDetail->goods_info);
                $agentGoodsInfo = self::getAgentGoodsInfo($orderDetail->goods_id,$goodsInfo);
                \Yii::warning("商品分销信息:".var_export($agentGoodsInfo,true));
                //商品是否单独设置了分销
                if ((isset($agentGoodsInfo['is_alone']) && $agentGoodsInfo['is_alone'] == 1)) {
                    \Yii::warning("商品单独设置了分销:".var_export($goodsAgentSetting->attributes,true));
                    //分销佣金类型1百分比0固定金额
                    $shareType = $agentGoodsInfo['share_type'];
                    if ($firstParentUser) {
                        //获取商品设置的一级分销金额
                        if (isset($agentGoodsInfo['commission_first'])) {
                            $first_price = $agentGoodsInfo['commission_first'];
                            $first_price = self::getAgentPrice($first_price, $shareType, $orderDetail);
                        } else {
                            $first_price = self::getAgentLevel($firstParentUser, $orderDetail,$shareType,'first_price');
                        }
                    }
                    if ($secondParentUser) {
                        //获取商品设置的二级分销金额
                        if (isset($agentGoodsInfo['commission_second'])) {
                            $second_price = $agentGoodsInfo['commission_second'];
                            $second_price = self::getAgentPrice($second_price, $shareType, $orderDetail);
                        } else {
                            $second_price = self::getAgentLevel($secondParentUser, $orderDetail,$shareType, 'second_price');
                        }
                    }
                    if ($thirdParentUser) {
                        //获取商品设置的三级分销金额
                        if (isset($agentGoodsInfo['commission_third'])) {
                            $third_price = $agentGoodsInfo['commission_third'];
                            $third_price = self::getAgentPrice($third_price, $shareType, $orderDetail);
                        } else {
                            $third_price = self::getAgentLevel($thirdParentUser, $orderDetail,$shareType, 'third_price');
                        }
                    }
                } else {
                    if ($order->mch_id > 0) {
                        continue;
                    }
                    \Yii::warning("商品没有单独设置分销:".var_export($goodsAgentSetting->attributes,true));
                    // 全局设置
                    $shareType = $agentSetting[StockSetting::PRICE_TYPE] == 2 ? 0 : 1;
                    if ($firstParentUser) {
                        if ($firstParentUser->level > 0) {
                            $first_price = self::getAgentLevelGlobal($firstParentUser, $orderDetail, StockSetting::FIRST_PRICE);
                        } else {
                            if (!empty($agentSetting[StockSetting::FIRST_PRICE])
                                && is_numeric($agentSetting[StockSetting::FIRST_PRICE])) {
                                $first_price = $agentSetting[StockSetting::FIRST_PRICE];
                                $first_price = self::getAgentPrice($first_price, $shareType, $orderDetail);
                            }
                        }
                    }

                    if ($secondParentUser) {
                        if ($secondParentUser->level > 0) {
                            $second_price = self::getAgentLevelGlobal($secondParentUser, $orderDetail, StockSetting::SECOND_PRICE);
                        } else {
                            if (!empty($agentSetting[StockSetting::SECOND_PRICE])
                                && is_numeric($agentSetting[StockSetting::SECOND_PRICE])) {
                                $second_price = $agentSetting[StockSetting::SECOND_PRICE];
                                $second_price = self::getAgentPrice($second_price, $shareType, $orderDetail);
                            }
                        }
                    }

                    if ($thirdParentUser) {
                        if ($thirdParentUser->level > 0) {
                            $third_price = self::getAgentLevelGlobal($thirdParentUser, $orderDetail, StockSetting::THIRD_PRICE);
                        } else {
                            if (!empty($agentSetting[StockSetting::THIRD_PRICE])
                                && is_numeric($agentSetting[StockSetting::THIRD_PRICE])) {
                                $third_price = $agentSetting[StockSetting::THIRD_PRICE];
                                $third_price = self::getAgentPrice($third_price, $shareType, $orderDetail);
                            }
                        }
                    }
                }
                //新增分销分佣订单记录
                $model = StockOrder::findOne([
                    'mall_id' => $order->mall_id,
                    'order_id' => $order->id,
                    'order_detail_id' => $orderDetail->id,
                    'user_id' => $order->user_id,
                    'is_delete' => 0,
                ]);
                if (!$model) {
                    $model = new StockOrder();
                    $model->mall_id = $order->mall_id;
                    $model->order_id = $order->id;
                    $model->user_id = $order->user_id;
                    $model->order_detail_id = $orderDetail->id;
                    $isNewInsert = true;
                }else{
                    $before_first_price = $model->first_price;
                    $before_second_price = $model->second_price;
                    $before_third_price = $model->third_price;
                    $before_first_parent_id = $model->first_parent_id;
                    $before_second_parent_id = $model->second_parent_id;
                    $before_third_parent_id = $model->third_parent_id;
                }

                if ($firstParentUser) {
                    $firstParentId = $firstParentUser->user_id;
                } else {
                    $firstParentId = 0;
                    $first_price = 0;
                }
                $model->first_parent_id = $firstParentId;
                $model->first_price = price_format($first_price);

                if ($secondParentUser) {
                    $secondParentId = $secondParentUser->user_id;
                } else {
                    $secondParentId = 0;
                    $second_price = 0;
                }
                $model->second_parent_id = $secondParentId;
                $model->second_price = price_format($second_price);

                if ($thirdParentUser) {
                    $thirdParentId = $thirdParentUser->user_id;
                } else {
                    $thirdParentId = 0;
                    $third_price = 0;
                }
                $model->third_parent_id = $thirdParentId;
                $model->third_price = price_format($third_price);

                $before = $model->oldAttributes;
                if (!$model->save()) {
                    throw new \Exception((new BaseModel())->responseErrorMsg($model));
                }
                $firstPriceTotal += $first_price;
                $secondPriceTotal += $second_price;
                $thirdPriceTotal += $third_price;
                $data = $model->attributes;
                if($isNewInsert){
                    $data["is_new_insert"] = $isNewInsert;
                }
                $data["before_first_price"] = $before_first_price;
                $data["before_second_price"] = $before_second_price;
                $data["before_third_price"] = $before_third_price;
                $data["before_first_parent_id"] = $before_first_parent_id;
                $data["before_second_parent_id"] = $before_second_parent_id;
                $data["before_third_parent_id"] = $before_third_parent_id;
                $agentOrderList[] = $data;
                \Yii::warning("添加分销分佣循环处理结束:".var_export($data,true));
            }

            \Yii::$app->trigger(AgentCommissionHandler::DISTRIBUTION_COMMISSION,
                new AgentCommissionEvent([
                    'mall' => \Yii::$app->mall,
                    'order' => $order,
                    'agentOrderList' => $agentOrderList,
                    'type' => 'add'
                ]));
            \Yii::warning("----添加分销分佣订单结束----");
        }catch (\Exception $ex){
            \Yii::error("添加分销分佣订单处理异常，异常信息：File:".$ex->getFile().";line:".$ex->getLine().";message:".$ex->getMessage());
        }
    }

    /**
     * 获取分销商所得分佣金额
     * @param $price
     * @param $shareType
     * @param OrderDetail $orderDetail
     * @return float
     */
    protected static function getAgentPrice($price, $shareType, $orderDetail)
    {
        $sharePrice = 0;
        if (!empty($price) && is_numeric($price) && $price > 0) {
            $sharePrice = $price;
        }

        if ($shareType == 1) {
            $sharePrice = $sharePrice * $orderDetail->total_price / 100;
        } else {
            $sharePrice = $sharePrice * $orderDetail->num;
        }
        return $sharePrice;
    }

    /**
     * 获取详细设置分销等级佣金
     * @param Stock $agent
     * @param OrderDetail $orderDetail
     * @param $shareType
     * @param string $key
     * @return int
     * @throws \Exception
     *
     */
    protected static function getAgentLevel($agent, $orderDetail,$shareType, $key)
    {
        $price = 0;
        $goods_agent_level = StockLevel::find()->where(["is_delete" => 0,"mall_id" => \Yii::$app->mall->id])->asArray()->all();
        if (!empty($goods_agent_level)) {
            $hasLevel = false;
            $first = 0;
            foreach ($goods_agent_level as $item) {
                if ($item['level'] == $agent->level) {
                    $hasLevel = true;
                    $price = $item[$key];
                    break;
                }
                if ($item['level'] <= 0) {
                    $first = $item[$key];
                }
            }
            // 判断是否有设置指定分销等级的佣金，若没有则使用默认佣金进行计算
            if (!$hasLevel) {
                $price = $first;
            }
        }
        return self::getAgentPrice($price, $shareType, $orderDetail);
    }

    /**
     * 获取全局设置分销佣金
     * @param Stock $agent
     * @param $orderDetail
     * @param $key
     * @return float|int
     */
    protected static function getAgentLevelGlobal($agent, $orderDetail, $key)
    {
        $agentLevel = StockLevelCommon::getInstance()->getAgentLevelByLevel($agent->level);
        if ($agentLevel) {
            $price = $agentLevel->$key;
            //分销佣金类型0或2固定金额1百分比
            $shareType = $agentLevel->price_type == 2 ? 0 : 1;
            return self::getAgentPrice($price, $shareType, $orderDetail);
        } else {
            return 0;
        }
    }

    /**
     * 更新分销订单
     * @param Order $order
     * @param $type
     * @param int $order_detail_id
     * @throws \Exception
     */
    public static function updateAgentOrder($order,$type,$order_detail_id = 0){
        \Yii::warning("-------更新分销订单开始-----");
        \Yii::warning("updateAgentOrder 类型={$type} 订单数据=".json_encode($order));
        $flag = "sub";
        $agentOrderList = [];
        try{
            //退款
            if($type == 2){
                $updateCondition = ["is_refund" => StockOrder::YES];
                $fields = ["order_detail_id" => $order_detail_id];
                $result = StockOrder::updateAll($updateCondition, $fields);
                if($result === false){
                    throw new \Exception('分销订单保存出错');
                }
                //分销订单
                $agentOrders = StockOrder::findOne([
                    'mall_id' => $order->mall_id,
                    'order_id' => $order->id,
                    'order_detail_id' => $order_detail_id,
                    'user_id' => $order->user_id,
                    'is_delete' => 0,
                ]);
                $agentOrderList = [$agentOrders];
            }else if($type == 3){ //发放
                $flag = "transfer";
                $result = StockOrder::updateAll(['is_transfer' => 1], [
                    'mall_id' => $order->mall_id, 'order_id' => $order->id, 'is_delete' => 0
                ]);
                if($result === false){
                    throw new \Exception('分销订单保存出错');
                }
                $agentOrderList = StockOrder::findAll([
                    'mall_id' => $order->mall_id, 'order_id' => $order->id, 'is_delete' => 0
                ]);
            }else if($type == 4){//更新支付状态
                $flag = "update_is_pay";
                $result = StockOrder::updateAll(['is_pay' => 1], [
                    'mall_id' => $order->mall_id, 'order_id' => $order->id, 'is_delete' => 0
                ]);
                if($result === false){
                    throw new \Exception('分销订单更新支付状态出错');
                }
                $agentOrderList = StockOrder::findAll([
                    'mall_id' => $order->mall_id, 'order_id' => $order->id, 'is_delete' => 0
                ]);
            }
            \Yii::warning("updateAgentOrder {$flag} 分销分佣订单数据=".json_encode($agentOrderList));
            \Yii::$app->trigger(AgentCommissionHandler::DISTRIBUTION_COMMISSION,
                new AgentCommissionEvent([
                    'mall' => \Yii::$app->mall,
                    'order' => $order,
                    'agentOrderList' => $agentOrderList,
                    'type' => $flag
                ]));
            \Yii::warning("-------更新分销订单结束-----");
        }catch (\Exception $ex){
            \Yii::error("更新分销订单处理异常，异常信息：File:".$ex->getFile().";line:".$ex->getLine().";message:".$ex->getMessage());
        }
    }

    /**
     * 获取商品分销设置
     * @param $goods_id
     * @param $goods_info
     * @return array
     */
    public static function getAgentGoodsInfo($goods_id,$goods_info){
        \Yii::warning("订单详情商品信息字段数据:".var_export($goods_info,true));
        $returnData = [];
        $commission_first = $commission_second = $commission_third = $level = 0;
        /** @var StockGoods $agentGoodsInfo */
        $agentGoodsInfo = StockGoods::find()->where(["goods_id" => $goods_id,"is_delete" => StockGoods::NO,
            'goods_type' => StockGoods::TYPE_MALL_GOODS])->one();
        if(!empty($agentGoodsInfo)){
            $returnData["is_alone"] = $agentGoodsInfo->is_alone;
            //是否单独设置
            if($agentGoodsInfo->is_alone == StockGoods::YES){
                //0普通设置（按商品）1详细设置（按商品规格）
                if($agentGoodsInfo->attr_setting_type == StockGoods::ATTR_SETTING_TYPE_GOODS){
                    \Yii::warning("分销按商品");
                    $agentGoodsDetail = StockGoodsDetail::find()->select([
                        'commission_first', 'commission_second', 'commission_third', 'level'
                    ])->where(['goods_id' => $goods_id, 'goods_attr_id' => 0, 'is_delete' => 0, 'goods_type' => StockGoodsDetail::TYPE_MALL_GOODS])->one();
                }else{
                    $attr_id = isset($goods_info["goods_attr"]["id"]) ? $goods_info["goods_attr"]["id"] : 0;
                    \Yii::warning("分销按商品规格 商品分类id:".$attr_id);
                    $agentGoodsDetail = StockGoodsDetail::find()->select([
                        'commission_first', 'commission_second', 'commission_third', 'level'
                    ])->where(['goods_id' => $goods_id, 'goods_attr_id' => $attr_id, 'goods_type' => StockGoodsDetail::TYPE_MALL_GOODS])->one();
                }
                \Yii::warning("分销商品单独设置:".var_export($agentGoodsDetail,true));
            }

            if(!empty($agentGoodsDetail)){
                $commission_first = $agentGoodsDetail->commission_first;
                $commission_second = $agentGoodsDetail->commission_second;
                $commission_third = $agentGoodsDetail->commission_third;
                $level = $agentGoodsDetail->level;
            }
            $returnData["share_type"] = $agentGoodsInfo->share_type;
            $returnData["commission_first"] = $commission_first;
            $returnData["commission_second"] = $commission_second;
            $returnData["commission_third"] = $commission_third;
            $returnData["level"] = $level;
        }
        return $returnData;
    }

}