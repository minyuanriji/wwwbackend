<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 经销佣金订单处理任务类
 * Author: zal
 * Date: 2020-05-25
 * Time: 17:05
 */

namespace app\plugins\boss\jobs;
use app\models\CommonOrderDetail;
use app\models\Mall;
use app\plugins\boss\models\Boss;
use app\plugins\boss\models\BossLevel;
use app\plugins\boss\models\BossOrderGoodsLog;
use app\plugins\boss\models\BossSetting;
use yii\base\Component;
use yii\queue\JobInterface;
use yii\queue\Queue;
class BossLogJob extends Component implements JobInterface
{
    /** @var CommonOrderDetail $order */
    public $order;
    public $common_order_detail_id;
    /** @var int 处理类型 1新增订单    2状态变更   3、一支付就结算 */
    public $type;
    public $mall;
    /**
     *
     * @param Queue $queue
     * @return mixed|void
     * @throws \Exception
     */

    //TODO 还需要加入其他筛选添加 例如是商城商品还是其他商品
    public function execute($queue)
    {
        \Yii::warning('执行股东分红商品记录队列');
        $this->order=CommonOrderDetail::findOne($this->common_order_detail_id);
        if(!$this->order){
            return;
        }
        $this->mall = Mall::findOne($this->order->mall_id);
        if (!$this->mall) {
            return;
        }
        $is_enable = BossSetting::getValueByKey(BossSetting::IS_ENABLE, $this->mall->id);
        if (!$is_enable) {
            \Yii::warning('系统没有开启股东提成');
            return;
        }
        //这里是订单状态改变
        if ($this->type == 2) {
            if ($this->order->status == 1) {
                $level_list = BossLevel::find()->where(['mall_id' => $this->mall->id, 'is_delete' => 0, 'is_enable' => 1])->all();
                foreach ($level_list as $level) {  //找到股东等级
                    /**
                     * @var BossLevel $level
                     */
                    $boss_list = Boss::find()->where(['mall_id' => $this->mall->id, 'is_delete' => 0, 'level' => $level->level])->all();
                    $boss_count = count($boss_list);
                    if ($boss_count && $boss_count > 0) {
                        foreach ($boss_list as $boss) {
                            /**
                             * @var Boss $boss
                             */
                            $log = new BossOrderGoodsLog();
                            $log->common_order_detail_id = $this->common_order_detail_id;
                            $log->price = $level->price;
                            $log->user_id = $boss->user_id;
                            $log->mall_id = $boss->mall_id;
                            $log->type = 0;
                            if (!$log->save()){
                                \Yii::warning('====='.json_encode($log->getErrors()));
                            }
                            if ($level->is_extra) {
                                $price = $level->extra_price;
                                if ($price && $price > 0) {
                                    $log = new BossOrderGoodsLog();
                                    $log->common_order_detail_id = $this->common_order_detail_id;
                                    $log->price = $level->extra_price;
                                    $log->user_id = $boss->user_id;
                                    $log->mall_id = $boss->mall_id;
                                    $log->type = 1;
                                    if (!$log->save()){
                                        \Yii::warning('====='.json_encode($log->getErrors()));
                                    }
                                }
                            }
                        }
                        unset($boss);
                    }
                }
                unset($level);
            }
        }
    }
}