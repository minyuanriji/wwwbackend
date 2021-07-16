<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 区域代理佣金订单处理任务类
 * Author: zal
 * Date: 2020-05-25
 * Time: 17:05
 */

namespace app\plugins\area\jobs;

use app\helpers\SerializeHelper;
use app\models\CommonOrderDetail;
use app\models\Mall;
use app\models\Order;
use app\models\PriceLog;
use app\models\User;
use app\models\UserAddress;
use app\models\UserChildren;
use app\plugins\area\models\AreaAgent;
use app\plugins\area\models\AreaGoods;
use app\plugins\area\models\AreaGoodsDetail;
use app\plugins\area\models\AreaSetting;
use app\plugins\area\Plugin;
use yii\base\Component;
use yii\base\Exception;
use yii\queue\JobInterface;
use yii\queue\Queue;

class AreaLogJob extends Component implements JobInterface
{
    /** @var CommonOrderDetail $order */
    public $order;
    public $common_order_detail_id;
    /** @var int 处理类型 1新增订单    2状态变更 */
    public $type;

    /**
     *
     * @param Queue $queue
     * @return mixed|void
     * @throws \Exception
     */

    //TODO 还需要加入其他筛选添加 例如是商城商品还是其他商品
    public function execute($queue)
    {
        \Yii::warning('-------------------------------------------------------------------------------------------------------');
        \Yii::warning('区域代理记录队列开始执行');
        $order = CommonOrderDetail::findOne($this->common_order_detail_id);
        if (!$order) {
            \Yii::warning("---公共订单不存在：{$this->common_order_detail_id}  商城ID{$this->order->mall_id}---");
            return;
        }
        $this->order = $order;
        $mall = Mall::findOne($this->order->mall_id);
        $plugin = new Plugin();
        $sign = 'mall';
        $sign = $plugin->getName();
        if (!$mall) {
            \Yii::warning("---处理区域代理队列时候商城不存在公共订单ID：{$this->common_order_detail_id} 商城ID{$this->order->mall_id}---");
            return;
        }
        \Yii::$app->setMall($mall);
        $is_enable = AreaSetting::getValueByKey(AreaSetting::IS_ENABLE);
        if (!$is_enable) {
            \Yii::warning('区域代理没有启用');
            return;
        }
        \Yii::warning("---区域代理佣金订单记录处理开始---");
        //这里需要从common_order_detail 里面获取商品的类型

        \Yii::warning('当前的type' . $this->type);
        $user = User::findOne($order->user_id);
        if (!$user) {
            \Yii::warning('区域代理订单找不到用户');
            return;
        }

        //1创建订单
        if ($this->type == 1) { //创建订单
            \Yii::warning('创建订单');
            //默认的分佣设置
            $is_level = AreaSetting::getValueByKey(AreaSetting::IS_LEVEL, $mall->id);//是否走级差
            $is_equal = AreaSetting::getValueByKey(AreaSetting::IS_EQUAL, $mall->id);//是否平均分
            $province_price = AreaSetting::getValueByKey(AreaSetting::PROVINCE_PRICE, $mall->id);//省级代理
            $city_price = intval(AreaSetting::getValueByKey(AreaSetting::CITY_PRICE, $mall->id));//市级代理
            $district_price = intval(AreaSetting::getValueByKey(AreaSetting::DISTRICT_PRICE, $mall->id));//区级代理
            $town_price = intval(AreaSetting::getValueByKey(AreaSetting::TOWN_PRICE, $mall->id));//镇级代理




            //现在是默认商城商品订单
            $is_alone = 0;
            $area_detail_list = null;
            $goods_type = $this->order->goods_type;
            $area_goods = null;
            $price_type = 0;
            if ($goods_type == CommonOrderDetail::TYPE_MALL_GOODS) {
                $area_goods = AreaGoods::findOne(['goods_id' => $this->order->goods_id, 'is_delete' => 0, 'is_alone' => 1, 'goods_type' => $goods_type]);  //这里要加入
                if ($area_goods) {
                    $area_goods_detail = AreaGoodsDetail::findOne(['area_goods_id' => $area_goods->id, 'is_delete' => 0]);  //这里要加入
                    $province_price = $area_goods_detail->province_price;
                    $city_price = $area_goods_detail->city_price;
                    $district_price = $area_goods_detail->district_price;
                    $town_price = $area_goods_detail->town_price;
                    $price_type = $area_goods->price_type;
                }
            }
            $province_id = 0;
            $city_id = 0;
            $district_id = 0;
            $town_id = 0;


            $is_town = 0;
            $is_district = 0;
            $is_city = 0;
            $is_province = 0;


            //商城商品
            if ($goods_type == CommonOrderDetail::TYPE_MALL_GOODS) {
                $mall_order = Order::findOne($order->order_id);
                if (!$mall_order) {
                    \Yii::warning('找不到订单');
                    return;
                }
                $user_address = UserAddress::findOne(['id' => $mall_order->address_id, 'is_delete' => 0]);
                if (!$user_address) {
                    \Yii::warning('未知的订单区域');
                    return;
                }
                $province_id = $user_address->province_id;
                $city_id = $user_address->city_id;
                $district_id = $user_address->district_id;
                $town_id = $user_address->town_id;
            }


            if ($price_type == 1) {
                $price = $order->num * $town_price;
            } else {
                $price = $order->price * $town_price / 100;
            }

            //  1 2 3 4
            $town_agent = AreaAgent::find()->where(['mall_id' => $mall->id, 'level' => AreaAgent::LEVEL_TOWN, 'town_id' => $town_id,'is_delete' => AreaAgent::IS_DELETE_NO])->all();
            if (count($town_agent) > 0) {
                if ($is_equal) {
                    $price = $price / count($town_agent);  //平均分
                }
                $is_town = 1;
            }

            foreach ($town_agent as $area) {
                /**
                 * @var AreaAgent $area
                 */
                $log = PriceLog::findOne(['common_order_detail_id' => $this->common_order_detail_id, 'is_delete' => 0, 'user_id' => $area->user_id, 'sign' => $sign]);
                $user_child_level = 0;
                $user_child = UserChildren::findOne(['user_id' => $area->user_id, 'child_id' => $order->user_id]);
                if ($user_child) {
                    $user_child_level = $user_child->level;
                }
                if (!$log) {
                    $log = new PriceLog();
                    $log->mall_id = \Yii::$app->mall->id;
                    $log->user_id = $area->user_id;
                    $log->status = 0;
                    $log->common_order_detail_id = $this->common_order_detail_id;
                    $log->child_id = $this->order->user_id;
                    $log->level = $user_child_level;
                    $log->order_id = $this->order->order_id;
                    $log->sign = $sign;
                    if ($price && $price > 0) {
                        $log->price = $price;
                    }
                    if ($log->save()) { //加钱
                        $user = User::findOne($log->user_id);
                        \Yii::$app->currency->setUser($user)->income
                            ->add(floatval($log->price), "区域代理商提成记录ID：{$log->id} 的冻结佣金", 0);
                        $area->frozen_price += $price;
                        $order_count = PriceLog::find()->where(['user_id' => $log->user_id, 'is_delete' => 0, 'sign' => $sign])->groupBy('order_id')->count();
                        $area->total_order = $order_count;
                        $area->save();
                        \Yii::warning('区域佣金保存成功');

                    }
                }
            }
            $district_price1 = $district_price;
            if ($is_level==1) {
                if ($is_town == 1) {
                    $district_price1 = $district_price - $town_price;
                } else {
                    $district_price1 = $district_price;
                }
            }
            if ($price_type == 1) {
                $price = $order->num * $district_price1;
            } else {
                $price = $order->price * $district_price1 / 100;
            }

            //  1 2 3 4
            $district_agent = AreaAgent::find()->where(['mall_id' => $mall->id, 'level' => AreaAgent::LEVEL_DISTRICT, 'district_id' => $district_id])->all();
            if (count($district_agent) > 0) {
                $is_district = 1;

                if ($is_equal) {
                    $price = $price / count($district_agent);  //平均分
                }
            }

            foreach ($district_agent as $area) {
                /**
                 * @var AreaAgent $area
                 */
                $log = PriceLog::findOne(['common_order_detail_id' => $this->common_order_detail_id, 'is_delete' => 0, 'user_id' => $area->user_id, 'sign' => $sign]);
                $user_child_level = 0;
                $user_child = UserChildren::findOne(['user_id' => $area->user_id, 'child_id' => $order->user_id]);
                if ($user_child) {
                    $user_child_level = $user_child->level;
                }
                if (!$log) {
                    $log = new PriceLog();
                    $log->mall_id = \Yii::$app->mall->id;
                    $log->user_id = $area->user_id;
                    $log->status = 0;
                    $log->common_order_detail_id = $this->common_order_detail_id;
                    $log->child_id = $this->order->user_id;
                    $log->level = $user_child_level;
                    $log->order_id = $this->order->order_id;
                    $log->sign = $sign;
                    if ($price && $price > 0) {
                        $log->price = $price;
                    }
                    if ($log->save()) { //加钱
                        $user = User::findOne($log->user_id);
                        \Yii::$app->currency->setUser($user)->income
                            ->add(floatval($log->price), "区域代理商提成记录ID：{$log->id} 的冻结佣金", 0);
                        $area->frozen_price += $price;
                        $order_count = PriceLog::find()->where(['user_id' => $log->user_id, 'is_delete' => 0, 'sign' => $sign])->groupBy('order_id')->count();
                        $area->total_order = $order_count;
                        $area->save();
                        \Yii::warning('区域佣金保存成功');
                    }
                }
            }
            //走级差的情况

            $city_price1 = $city_price;
            if ($is_level) {
                if ($is_district == 1) {
                    $city_price1 = $city_price - $district_price;
                } else {
                    if ($is_town == 1) {
                        $city_price1 = $city_price - $town_price;
                    }
                }
            }
            if ($price_type == 1) {
                $price = $order->num * $city_price1;
            } else {
                $price = $order->price * $city_price1 / 100;
            }
            $city_agent = AreaAgent::find()->where(['mall_id' => $mall->id, 'level' => AreaAgent::LEVEL_CITY, 'city_id' => $city_id])->all();
            if (count($city_agent) > 0) {
                $is_city = 1;
                if ($is_equal) {
                    $price = $price / count($city_agent);  //平均分
                }
            }



            foreach ($city_agent as $area) {
                /**
                 * @var AreaAgent $area
                 */
                $log = PriceLog::findOne(['common_order_detail_id' => $this->common_order_detail_id, 'is_delete' => 0, 'user_id' => $area->user_id, 'sign' => $sign]);
                $user_child_level = 0;
                $user_child = UserChildren::findOne(['user_id' => $area->user_id, 'child_id' => $order->user_id]);
                if ($user_child) {
                    $user_child_level = $user_child->level;
                }
                if (!$log) {
                    $log = new PriceLog();
                    $log->mall_id = \Yii::$app->mall->id;
                    $log->user_id = $area->user_id;
                    $log->status = 0;
                    $log->common_order_detail_id = $this->common_order_detail_id;
                    $log->child_id = $this->order->user_id;
                    $log->level = $user_child_level;
                    $log->order_id = $this->order->order_id;
                    $log->sign = $sign;
                    if ($price && $price > 0) {
                        $log->price = $price;
                    }
                    if ($log->save()) { //加钱
                        $user = User::findOne($log->user_id);
                        \Yii::$app->currency->setUser($user)->income
                            ->add(floatval($log->price), "区域代理商提成记录ID：{$log->id} 的冻结佣金", 0);
                        $area->frozen_price += $price;
                        $order_count = PriceLog::find()->where(['user_id' => $log->user_id, 'is_delete' => 0, 'sign' => $sign])->groupBy('order_id')->count();
                        $area->total_order = $order_count;
                        $area->save();
                        \Yii::warning('区域佣金保存成功');
                    }
                }
            }
            //走级差的情况

            $province_price1 = $province_price;
            if ($is_level) {
                if ($is_city) {
                    $province_price1 = $province_price - $city_price;
                } else {
                    if ($is_district) {
                        $province_price1 = $province_price - $district_price;
                    } else {
                        if ($is_town) {
                            $province_price1 = $province_price - $town_price;
                        }
                    }
                }
            }


            if ($price_type == 1) {
                $price = $order->num * $province_price1;
            } else {
                $price = $order->price * $province_price1 / 100;
            }
            $province_agent = AreaAgent::find()->where(['mall_id' => $mall->id, 'level' => AreaAgent::LEVEL_PROVINCE, 'province_id' => $province_id])->all();
            if (count($province_agent) > 0) {
                if ($is_equal) {
                    $price = $price / count($province_agent);  //平均分
                }
            }
            foreach ($province_agent as $area) {
                /**
                 * @var AreaAgent $area
                 */
                $log = PriceLog::findOne(['common_order_detail_id' => $this->common_order_detail_id, 'is_delete' => 0, 'user_id' => $area->user_id, 'sign' => $sign]);
                $user_child_level = 0;
                $user_child = UserChildren::findOne(['user_id' => $area->user_id, 'child_id' => $order->user_id]);
                if ($user_child) {
                    $user_child_level = $user_child->level;
                }
                if (!$log) {
                    $log = new PriceLog();
                    $log->mall_id = \Yii::$app->mall->id;
                    $log->user_id = $area->user_id;
                    $log->status = 0;
                    $log->common_order_detail_id = $this->common_order_detail_id;
                    $log->child_id = $this->order->user_id;
                    $log->level = $user_child_level;
                    $log->order_id = $this->order->order_id;
                    $log->sign = $sign;
                    if ($price && $price > 0) {
                        $log->price = $price;
                    }
                    if ($log->save()) { //加钱
                        \Yii::warning('区域佣金保存成功之后，执行保存冻结佣金');
                        $user = User::findOne($log->user_id);
                        \Yii::$app->currency->setUser($user)->income
                            ->add(floatval($log->price), "区域代理商提成记录ID：{$log->id} 的冻结佣金", 0);
                        $area->frozen_price += $price;
                        $order_count = PriceLog::find()->where(['user_id' => $log->user_id, 'is_delete' => 0, 'sign' => $sign])->groupBy('order_id')->count();
                        $area->total_order = $order_count;
                        if (!$area->save()) {
                            \Yii::warning('区域冻结佣金保存失败.' . $area->getErrors());
                        }
                        \Yii::warning('区域佣金保存成功');
                    }
                }
            }
        }
        //这里是订单状态改变
        if ($this->type == 2) {
            //有效   更改当前的区域代理记录状态
            $log_list = PriceLog::find()->andWhere(['common_order_detail_id' => $this->common_order_detail_id, 'is_delete' => 0, 'status' => 0, 'sign' => $sign])->all();
            foreach ($log_list as $log) {
                if ($this->order->status == 1) {
                    \Yii::warning('订单符合分润');
                    /**
                     * @var PriceLog $log
                     */
                    $log->status = 1;
                    //开始佣金到账
                    $user = User::findOne($log->user_id);
                    \Yii::$app->currency->setUser($user)->income
                        ->add(floatval($log->price), "区域代理商提成记录ID：{$log->id} 的佣金发放", 0, 1);
                    $log->is_price = 1;
                    if (!$log->save()) {
                        \Yii::warning('佣金记录发放保存失败：' . SerializeHelper::encode($log->getErrors()));
                    } else {
                        \Yii::warning('日志保存成功Le');
                        $area = AreaAgent::findOne(['user_id' => $log->user_id, 'is_delete' => 0]);
                        if ($area) {
                            try {
                                \Yii::warning('找到区域代理商');
                                $area->total_price += floatval($log->price);
                                $area->frozen_price -= floatval($log->price);
                                if (!$area->save()) {
                                    \Yii::warning(SerializeHelper::encode($area));
                                }
                            } catch (Exception $e) {
                                \Yii::warning($e->getMessage());
                            }
                        } else {
                            \Yii::warning('找不到区域代理商');
                        }
                    }
                }
                if ($this->order->status == -1) {
                    /**
                     * @var PriceLog $log
                     */
                    $log->status = -1;
                    //开始佣金到账
                    if (!$log->save()) {
                        \Yii::warning('佣金记录保存失败：' . SerializeHelper::encode($log->getErrors()));
                    } else {
                        //保存成功之后要减掉冻结的钱
                        $user = User::findOne($log->user_id);
                        \Yii::$app->currency->setUser($user)->income
                            ->refund(floatval($log->price), "分佣记录ID：{$log->id} 的冻结佣金扣除", 0, 0);
                        $area = AreaAgent::findOne(['user_id' => $log->user_id, 'is_delete' => 0]);
                        if ($area) {
                            $area->frozen_price -= floatval($log->price);
                            if (!$area->save()) {
                                \Yii::warning(SerializeHelper::encode($area));
                            }
                        }
                    }
                }
            }
        }
        if ($this->type == 3) {

           //有效   更改当前的区域代理记录状态
            $log_list = PriceLog::find()->andWhere(['common_order_detail_id' => $this->common_order_detail_id, 'is_delete' => 0, 'status' => 0, 'sign' => $sign])->all();
            foreach ($log_list as $log) {
                if ($this->order->status == 0) {
                    \Yii::warning('订单符合分润');
                    /**
                     * @var PriceLog $log
                     */
                    $log->status = 1;
                    //开始佣金到账
                    $user = User::findOne($log->user_id);
                    \Yii::$app->currency->setUser($user)->income
                        ->add(floatval($log->price), "区域代理商提成记录ID：{$log->id} 的佣金发放", 0, 1);
                    $log->is_price = 1;
                    if (!$log->save()) {
                        \Yii::warning('佣金记录发放保存失败：' . SerializeHelper::encode($log->getErrors()));
                    } else {
                        \Yii::warning('日志保存成功Le');
                        $area = AreaAgent::findOne(['user_id' => $log->user_id, 'is_delete' => 0]);
                        if ($area) {
                            try {
                                \Yii::warning('找到区域代理商');
                                $area->total_price += floatval($log->price);
                                $area->frozen_price -= floatval($log->price);
                                if (!$area->save()) {
                                    \Yii::warning(SerializeHelper::encode($area));
                                }
                            } catch (Exception $e) {
                                \Yii::warning($e->getMessage());
                            }
                        } else {
                            \Yii::warning('找不到区域代理商');
                        }
                    }
                }

            }
        }

    }
}