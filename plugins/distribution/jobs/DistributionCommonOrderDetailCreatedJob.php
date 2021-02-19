<?php
namespace app\plugins\distribution\jobs;

use app\models\CommonOrderDetail;
use app\models\Mall;
use app\models\PriceLog;
use app\models\User;
use app\plugins\distribution\models\Distribution;
use app\plugins\distribution\models\DistributionGoods;
use app\plugins\distribution\models\DistributionGoodsDetail;
use app\plugins\distribution\models\DistributionLevel;
use app\plugins\distribution\models\DistributionSetting;
use app\plugins\distribution\Plugin;
use yii\base\Component;
use yii\queue\JobInterface;

class DistributionCommonOrderDetailCreatedJob extends Component implements JobInterface{

    public $common_order_detail_id;
    public $order;

    public function execute($queue){

        \Yii::warning('-------------------------------------------------------------------------------------------------------');
        \Yii::warning('分销记录队列开始执行，common_order_detail_id：'.$this->common_order_detail_id);
        $order = CommonOrderDetail::findOne($this->common_order_detail_id);
        \Yii::warning('分销订单数据'.var_export($order,true));
        if (!$order) {
            \Yii::warning("---公共订单不存在：{$this->common_order_detail_id}---");
            return;
        }
        $this->order = $order;
        $mall = Mall::findOne($this->order->mall_id);
        $plugin = new Plugin();
        $sign = 'mall';
        $sign = $plugin->getName();
        if (!$mall) {
            \Yii::warning("---处理分销队列时候商城不存在公共订单ID：{$this->common_order_detail_id} 商城ID{$this->order->mall_id}---");
            return;
        }
        \Yii::$app->setMall($mall);
        \Yii::warning("---分销佣金订单记录处理开始---");
        //这里需要从common_order_detail 里面获取商品的类型

        $user = User::findOne($order->user_id);
        if (!$user) {
            \Yii::warning('分销订单找不到用户');
            return;
        }

        \Yii::warning('创建订单');
        //默认的分佣设置
        $level = DistributionSetting::getValueByKey(DistributionSetting::LEVEL);
        $first_price1 = DistributionSetting::getValueByKey(DistributionSetting::FIRST_PRICE);
        $second_price1 = DistributionSetting::getValueByKey(DistributionSetting::SECOND_PRICE);
        $third_price1 = DistributionSetting::getValueByKey(DistributionSetting::THIRD_PRICE);
        $price_type = DistributionSetting::getValueByKey(DistributionSetting::PRICE_TYPE);
        $is_self_buy = DistributionSetting::getValueByKey(DistributionSetting::IS_SELF_BUY);
        \Yii::warning('分销层级******************************************************'.$level);
        if (empty($level) || $level < 1) {
            \Yii::error("未开启分销 订单数据：" . json_encode($this->order));
            return false;
        }
        //现在是默认商城商品订单
        $is_alone = 0;
        $distribution_detail_list = null;
        $goods_type = $this->order->goods_type; // 优化好common_order_detail那个表之后这里切换回这行代码
        //  $goods_type = 0;  //商品类型
        \Yii::error("分销 goods_type 订单数据：$goods_type=" . $goods_type . ",TYPE_MALL_GOODS=".CommonOrderDetail::TYPE_MALL_GOODS);
        if ($goods_type == CommonOrderDetail::TYPE_MALL_GOODS) {
            //商城商品
            $distribution_goods = DistributionGoods::findOne(['goods_id' => $this->order->goods_id, 'is_delete' => 0, 'is_alone' => 1]);  //这里要加入
            \Yii::error("分销商 distribution_goods 数据：" . var_export($distribution_goods,true));
            if ($distribution_goods) {
                //独立设置
                $is_alone = 1;
                $distribution_detail_list = DistributionGoodsDetail::find()->andWhere(['distribution_goods_id' => $distribution_goods->id, 'is_delete' => 0])->all();
                \Yii::error("分销商 distribution_detail_list 数据：" . var_export($distribution_detail_list,true));
            }
        }
        $distribution_list = [];
        //先找出分销商
        if ($is_self_buy) {
            $distribution1 = Distribution::findOne(['user_id' => $this->order->user_id, 'is_delete' => 0]);
            if ($distribution1) {
                $distribution_list[0] = $distribution1;
            } else {
                return;
            }
            $distribution2 = Distribution::findOne(['user_id' => $user->parent_id, 'is_delete' => 0]);//二级
            if ($distribution2) {
                $distribution_list[1] = $distribution2;
                $parent2 = User::findOne($distribution2->user_id);
                if ($parent2) {
                    $distribution3 = Distribution::findOne(['user_id' => $parent2->parent_id, 'is_delete' => 0]);//二级
                    if ($distribution3) {
                        $distribution_list[2] = $distribution3;
                    }
                }
            }
        } else {

            $distribution1 = Distribution::findOne(['user_id' => $user->parent_id, 'is_delete' => 0]);//一级
            \Yii::error("分销商 Distribution 数据1：" . var_export($distribution1,true));
            if ($distribution1) {
                $distribution_list[0] = $distribution1;
                $parent1 = User::findOne($distribution1->user_id);
                if ($parent1) {
                    $distribution2 = Distribution::findOne(['user_id' => $parent1->parent_id, 'is_delete' => 0]);//二级
                    \Yii::error("分销商 Distribution 数据2：" . var_export($distribution2,true));
                    if ($distribution2) {
                        $distribution_list[1] = $distribution2;
                        $parent2 = User::findOne($distribution2->user_id);
                        if ($parent2) {
                            $distribution3 = Distribution::findOne(['user_id' => $parent2->parent_id, 'is_delete' => 0]);//二级
                            if ($distribution3) {
                                \Yii::error("分销商 Distribution 数据3：" . var_export($distribution3,true));
                                $distribution_list[2] = $distribution3;
                            }
                        }
                    }
                }
            }
        }

        for ($i = 0; $i < $level; $i++) {
            $is_level = 0;
            //用户层级
            $user_level = $i + 1;
            if (count($distribution_list) > $i) {
                /**
                 * @var Distribution $distribution
                 */
                $distribution = $distribution_list[$i];
                if ($distribution) {
                    $log = PriceLog::findOne(['common_order_detail_id' => $this->common_order_detail_id, 'is_delete' => 0, 'user_id' => $distribution->user_id,'sign'=>$sign]);
                    $price = 0;
                    if (!$log) {
                        $log = new PriceLog();
                        $log->mall_id = \Yii::$app->mall->id;
                        $log->user_id = $distribution->user_id;
                        $log->status = 0;
                        $log->common_order_detail_id = $this->common_order_detail_id;
                        $log->child_id = $this->order->user_id;
                        $log->level = $user_level;
                        $log->order_id = $this->order->order_id;
                        $log->sign = $sign;
                    }
                    if ($is_alone) {//单独设置的
                        \Yii::warning('启用了独立分销机制******************************************************');
                        $first_price = 0;
                        $second_price = 0;
                        $third_price = 0;
                        $price_type = $distribution_goods->share_type == 0 ? 2 : 1;
                        if ($distribution_goods->attr_setting_type == 1) {
                            //按照规格
                            foreach ($distribution_detail_list as $detail) {
                                /**
                                 * @var  DistributionGoodsDetail $detail
                                 */
                                //\Yii::warning('启用了独立分销机制,按照规格:'.var_export($detail,true).'******************************************************');
                                if ($detail->level == $distribution->level && $detail->goods_attr_id == $this->order->attr_id) {
                                    $first_price2 = $detail->commission_first;
                                    $second_price2 = $detail->commission_second;
                                    $third_price2 = $detail->commission_third;
                                    $is_level = 1;
                                }
                            }
                        } else {
                            //不是按规格
                            foreach ($distribution_detail_list as $detail) {
                                /**
                                 * @var  DistributionGoodsDetail $detail
                                 */
                                if ($detail->level == $distribution->level && $detail->goods_attr_id == 0) {
                                    $first_price2 = $detail->commission_first;
                                    $second_price2 = $detail->commission_second;
                                    $third_price2 = $detail->commission_third;
                                    $is_level = 1;
                                    break;
                                }
                            }
                        }
                    } else {
                        $distribution_level = DistributionLevel::findOne(['level' => $distribution->level, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
                        if ($distribution_level) { //找到分销商等级
                            $first_price2 = $distribution_level->first_price;
                            $second_price2 = $distribution_level->second_price;
                            $third_price2 = $distribution_level->third_price;
                            $price_type = $distribution_level->price_type;
                            $is_level = 1;
                        }
                    }

                    if ($is_level) {
                        $first_price = $first_price2;
                        $second_price = $second_price2;
                        $third_price = $third_price2;
                    }else{
                        $first_price = $first_price1;
                        $second_price = $second_price1;
                        $third_price = $third_price1;
                        $price_type = DistributionSetting::getValueByKey(DistributionSetting::PRICE_TYPE);
                    }

                    //公共的部分
                    if ($price_type == 2) {
                        //固定金额
                        if ($user_level == 1) { //一级
                            $price = $first_price * $this->order->num;
                        }
                        if ($user_level == 2) { //二级
                            $price = $second_price * $this->order->num;
                        }
                        if ($user_level == 3) { //三级
                            $price = $third_price * $this->order->num;
                        }
                    }

                    if ($price_type == 1) {
                        //百分比金额
                        if ($user_level == 1) { //一级
                            $price = $first_price * $this->order->price / 100;
                        }
                        if ($user_level == 2) { //二级
                            $price = $second_price * $this->order->price / 100;;
                        }
                        if ($user_level == 3) { //三级
                            $price = $third_price * $this->order->price / 100;;
                        }
                    }

                    if($log->level == $user_level){
                        $log->price = $price;
                    }

                    if (!$log->save()) {
                        \Yii::warning(json_encode($log->getErrors()));
                    } else {
                        $distribution->frozen_price = 0;
                        $distribution->total_order  = 0;
                        $distribution->save();
                        \Yii::warning('保存成功');
                    }
                }
            }
        }
    }
}