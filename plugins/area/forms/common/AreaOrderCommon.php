<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销佣金订单记录公共处理类
 * Author: zal
 * Date: 2020-05-26
 * Time: 10:30
 */

namespace app\plugins\area\forms\common;

use app\models\BaseModel;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use app\plugins\area\events\AreaCommissionEvent;
use app\plugins\area\handlers\AreaCommissionHandler;
use app\plugins\area\models\Area;
use app\plugins\area\models\AreaGoods;
use app\plugins\area\models\AreaGoodsDetail;
use app\plugins\area\models\AreaLevel;
use app\plugins\area\models\AreaOrder;
use app\plugins\area\models\AreaSetting;
use app\plugins\mch\models\Goods;

class AreaOrderCommon extends BaseModel
{

    /**
     * 添加分销分佣订单
     * @param Order $order
     * @param $level
     * @throws \Exception
     */
    public static function addAreaOrder($order,$level){
        \Yii::warning("----添加分销分佣订单开始----");
        $firstParentUser = $secondParentUser = $thirdParentUser = [];
        try{
            //是否开启分销内购
            $areaSetting = AreaSetting::getValueByKey(AreaSetting::IS_SELF_BUY);
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
            $area = isset($user->area) ? $user->area : [];
            //$score = Area::find()->where(['mall_id' => $order->mall_id, 'user_id' => $user_id, 'is_delete' => 0])->with("user")->one();
            if ((!empty($areaSetting) && $areaSetting == 1) && !empty($area)) {
                //开启分销内购，自己是第一级，上级为第二级，上上级为第三级
                $firstParentUser = $area;
                $second_user_id = $user->parent_id;
                $third_user_id = $user->second_parent_id;
            }else{
                //没有满足分销内购条件，则按级别进行分销
                $firstParentUser = Area::findOne(['mall_id' => $order->mall_id,'user_id' => $user->parent_id, 'is_delete' => 0]);
            }

            if(empty($firstParentUser)){
                return;
            }

            $secondParentUser = $thirdParentUser = [];
            //分销设置
            $areaSetting = AreaSetting::getData($order->mall_id);
            //分销等级
            if($level >= 2){
                if($second_user_id > 0){
                    $secondParentUser = Area::findOne(['mall_id' => $order->mall_id,'user_id' => $second_user_id, 'is_delete' => 0]);
                }
                if($third_user_id > 0){
                    $thirdParentUser = Area::findOne(['mall_id' => $order->mall_id,'user_id' => $third_user_id, 'is_delete' => 0]);
                }
            }
            //订单详情
            $orderDetails = $order->getDetail()->with(
                ['goods' => function ($query) {
                    $query->with(['score']);
                }])->andWhere(['is_delete' => 0])->all();
            $firstPriceTotal = 0;
            $secondPriceTotal = 0;
            $thirdPriceTotal = 0;
            $areaOrderList = [];
            /** @var OrderDetail $orderDetail */
            foreach ($orderDetails as $orderDetail) {
                \Yii::warning("添加分销分佣循环处理开始:".var_export($orderDetail,true));
                $isNewInsert = false;
                $data = [];
                /** @var Goods $goodsAreaSetting */
                $goodsAreaSetting = $orderDetail->goods;
                $first_price = 0;
                $second_price = 0;
                $third_price = 0;
                $before_first_price = $before_second_price = $before_third_price = 0;
                $before_first_parent_id = $before_second_parent_id = $before_third_parent_id = 0;
                $shareType = 0;
                $goodsInfo = $orderDetail->decodeGoodsInfo($orderDetail->goods_info);
                $areaGoodsInfo = self::getAreaGoodsInfo($orderDetail->goods_id,$goodsInfo);
                \Yii::warning("商品分销信息:".var_export($areaGoodsInfo,true));
                //商品是否单独设置了分销
                if ((isset($areaGoodsInfo['is_alone']) && $areaGoodsInfo['is_alone'] == 1)) {
                    \Yii::warning("商品单独设置了分销:".var_export($goodsAreaSetting->attributes,true));
                    //分销佣金类型1百分比0固定金额
                    $shareType = $areaGoodsInfo['share_type'];
                    if ($firstParentUser) {
                        //获取商品设置的一级分销金额
                        if (isset($areaGoodsInfo['commission_first'])) {
                            $first_price = $areaGoodsInfo['commission_first'];
                            $first_price = self::getAreaPrice($first_price, $shareType, $orderDetail);
                        } else {
                            $first_price = self::getAreaLevel($firstParentUser, $orderDetail,$shareType,'first_price');
                        }
                    }
                    if ($secondParentUser) {
                        //获取商品设置的二级分销金额
                        if (isset($areaGoodsInfo['commission_second'])) {
                            $second_price = $areaGoodsInfo['commission_second'];
                            $second_price = self::getAreaPrice($second_price, $shareType, $orderDetail);
                        } else {
                            $second_price = self::getAreaLevel($secondParentUser, $orderDetail,$shareType, 'second_price');
                        }
                    }
                    if ($thirdParentUser) {
                        //获取商品设置的三级分销金额
                        if (isset($areaGoodsInfo['commission_third'])) {
                            $third_price = $areaGoodsInfo['commission_third'];
                            $third_price = self::getAreaPrice($third_price, $shareType, $orderDetail);
                        } else {
                            $third_price = self::getAreaLevel($thirdParentUser, $orderDetail,$shareType, 'third_price');
                        }
                    }
                } else {
                    if ($order->mch_id > 0) {
                        continue;
                    }
                    \Yii::warning("商品没有单独设置分销:".var_export($goodsAreaSetting->attributes,true));
                    // 全局设置
                    $shareType = $areaSetting[AreaSetting::PRICE_TYPE] == 2 ? 0 : 1;
                    if ($firstParentUser) {
                        if ($firstParentUser->level > 0) {
                            $first_price = self::getAreaLevelGlobal($firstParentUser, $orderDetail, AreaSetting::FIRST_PRICE);
                        } else {
                            if (!empty($areaSetting[AreaSetting::FIRST_PRICE])
                                && is_numeric($areaSetting[AreaSetting::FIRST_PRICE])) {
                                $first_price = $areaSetting[AreaSetting::FIRST_PRICE];
                                $first_price = self::getAreaPrice($first_price, $shareType, $orderDetail);
                            }
                        }
                    }

                    if ($secondParentUser) {
                        if ($secondParentUser->level > 0) {
                            $second_price = self::getAreaLevelGlobal($secondParentUser, $orderDetail, AreaSetting::SECOND_PRICE);
                        } else {
                            if (!empty($areaSetting[AreaSetting::SECOND_PRICE])
                                && is_numeric($areaSetting[AreaSetting::SECOND_PRICE])) {
                                $second_price = $areaSetting[AreaSetting::SECOND_PRICE];
                                $second_price = self::getAreaPrice($second_price, $shareType, $orderDetail);
                            }
                        }
                    }

                    if ($thirdParentUser) {
                        if ($thirdParentUser->level > 0) {
                            $third_price = self::getAreaLevelGlobal($thirdParentUser, $orderDetail, AreaSetting::THIRD_PRICE);
                        } else {
                            if (!empty($areaSetting[AreaSetting::THIRD_PRICE])
                                && is_numeric($areaSetting[AreaSetting::THIRD_PRICE])) {
                                $third_price = $areaSetting[AreaSetting::THIRD_PRICE];
                                $third_price = self::getAreaPrice($third_price, $shareType, $orderDetail);
                            }
                        }
                    }
                }
                //新增分销分佣订单记录
                $model = AreaOrder::findOne([
                    'mall_id' => $order->mall_id,
                    'order_id' => $order->id,
                    'order_detail_id' => $orderDetail->id,
                    'user_id' => $order->user_id,
                    'is_delete' => 0,
                ]);
                if (!$model) {
                    $model = new AreaOrder();
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
                $areaOrderList[] = $data;
                \Yii::warning("添加分销分佣循环处理结束:".var_export($data,true));
            }

            \Yii::$app->trigger(AreaCommissionHandler::DISTRIBUTION_COMMISSION,
                new AreaCommissionEvent([
                    'mall' => \Yii::$app->mall,
                    'order' => $order,
                    'areaOrderList' => $areaOrderList,
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
    protected static function getAreaPrice($price, $shareType, $orderDetail)
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
     * @param Area $area
     * @param OrderDetail $orderDetail
     * @param $shareType
     * @param string $key
     * @return int
     * @throws \Exception
     *
     */
    protected static function getAreaLevel($area, $orderDetail,$shareType, $key)
    {
        $price = 0;
        $goods_area_level = AreaLevel::find()->where(["is_delete" => 0,"mall_id" => \Yii::$app->mall->id])->asArray()->all();
        if (!empty($goods_area_level)) {
            $hasLevel = false;
            $first = 0;
            foreach ($goods_area_level as $item) {
                if ($item['level'] == $area->level) {
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
        return self::getAreaPrice($price, $shareType, $orderDetail);
    }

    /**
     * 获取全局设置分销佣金
     * @param Area $area
     * @param $orderDetail
     * @param $key
     * @return float|int
     */
    protected static function getAreaLevelGlobal($area, $orderDetail, $key)
    {
        $areaLevel = AreaLevelCommon::getInstance()->getAreaLevelByLevel($area->level);
        if ($areaLevel) {
            $price = $areaLevel->$key;
            //分销佣金类型0或2固定金额1百分比
            $shareType = $areaLevel->price_type == 2 ? 0 : 1;
            return self::getAreaPrice($price, $shareType, $orderDetail);
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
    public static function updateAreaOrder($order,$type,$order_detail_id = 0){
        \Yii::warning("-------更新分销订单开始-----");
        \Yii::warning("updateAreaOrder 类型={$type} 订单数据=".json_encode($order));
        $flag = "sub";
        $areaOrderList = [];
        try{
            //退款
            if($type == 2){
                $updateCondition = ["is_refund" => AreaOrder::YES];
                $fields = ["order_detail_id" => $order_detail_id];
                $result = AreaOrder::updateAll($updateCondition, $fields);
                if($result === false){
                    throw new \Exception('分销订单保存出错');
                }
                //分销订单
                $areaOrders = AreaOrder::findOne([
                    'mall_id' => $order->mall_id,
                    'order_id' => $order->id,
                    'order_detail_id' => $order_detail_id,
                    'user_id' => $order->user_id,
                    'is_delete' => 0,
                ]);
                $areaOrderList = [$areaOrders];
            }else if($type == 3){ //发放
                $flag = "transfer";
                $result = AreaOrder::updateAll(['is_transfer' => 1], [
                    'mall_id' => $order->mall_id, 'order_id' => $order->id, 'is_delete' => 0
                ]);
                if($result === false){
                    throw new \Exception('分销订单保存出错');
                }
                $areaOrderList = AreaOrder::findAll([
                    'mall_id' => $order->mall_id, 'order_id' => $order->id, 'is_delete' => 0
                ]);
            }else if($type == 4){//更新支付状态
                $flag = "update_is_pay";
                $result = AreaOrder::updateAll(['is_pay' => 1], [
                    'mall_id' => $order->mall_id, 'order_id' => $order->id, 'is_delete' => 0
                ]);
                if($result === false){
                    throw new \Exception('分销订单更新支付状态出错');
                }
                $areaOrderList = AreaOrder::findAll([
                    'mall_id' => $order->mall_id, 'order_id' => $order->id, 'is_delete' => 0
                ]);
            }
            \Yii::warning("updateAreaOrder {$flag} 分销分佣订单数据=".json_encode($areaOrderList));
            \Yii::$app->trigger(AreaCommissionHandler::DISTRIBUTION_COMMISSION,
                new AreaCommissionEvent([
                    'mall' => \Yii::$app->mall,
                    'order' => $order,
                    'areaOrderList' => $areaOrderList,
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
    public static function getAreaGoodsInfo($goods_id,$goods_info){
        \Yii::warning("订单详情商品信息字段数据:".var_export($goods_info,true));
        $returnData = [];
        $commission_first = $commission_second = $commission_third = $level = 0;
        /** @var AreaGoods $areaGoodsInfo */
        $areaGoodsInfo = AreaGoods::find()->where(["goods_id" => $goods_id,"is_delete" => AreaGoods::NO,
            'goods_type' => AreaGoods::TYPE_MALL_GOODS])->one();
        if(!empty($areaGoodsInfo)){
            $returnData["is_alone"] = $areaGoodsInfo->is_alone;
            //是否单独设置
            if($areaGoodsInfo->is_alone == AreaGoods::YES){
                //0普通设置（按商品）1详细设置（按商品规格）
                if($areaGoodsInfo->attr_setting_type == AreaGoods::ATTR_SETTING_TYPE_GOODS){
                    \Yii::warning("分销按商品");
                    $areaGoodsDetail = AreaGoodsDetail::find()->select([
                        'commission_first', 'commission_second', 'commission_third', 'level'
                    ])->where(['goods_id' => $goods_id, 'goods_attr_id' => 0, 'is_delete' => 0, 'goods_type' => AreaGoodsDetail::TYPE_MALL_GOODS])->one();
                }else{
                    $attr_id = isset($goods_info["goods_attr"]["id"]) ? $goods_info["goods_attr"]["id"] : 0;
                    \Yii::warning("分销按商品规格 商品分类id:".$attr_id);
                    $areaGoodsDetail = AreaGoodsDetail::find()->select([
                        'commission_first', 'commission_second', 'commission_third', 'level'
                    ])->where(['goods_id' => $goods_id, 'goods_attr_id' => $attr_id, 'goods_type' => AreaGoodsDetail::TYPE_MALL_GOODS])->one();
                }
                \Yii::warning("分销商品单独设置:".var_export($areaGoodsDetail,true));
            }

            if(!empty($areaGoodsDetail)){
                $commission_first = $areaGoodsDetail->commission_first;
                $commission_second = $areaGoodsDetail->commission_second;
                $commission_third = $areaGoodsDetail->commission_third;
                $level = $areaGoodsDetail->level;
            }
            $returnData["share_type"] = $areaGoodsInfo->share_type;
            $returnData["commission_first"] = $commission_first;
            $returnData["commission_second"] = $commission_second;
            $returnData["commission_third"] = $commission_third;
            $returnData["level"] = $level;
        }
        return $returnData;
    }

}