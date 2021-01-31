<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销佣金订单处理任务类
 * Author: zal
 * Date: 2020-05-25
 * Time: 17:05
 */

namespace app\plugins\distribution\jobs;

use app\helpers\SerializeHelper;
use app\models\CommonOrderDetail;
use app\models\Mall;
use app\models\PriceLog;
use app\models\User;
use app\models\UserParent;
use app\plugins\distribution\models\Distribution;
use app\plugins\distribution\models\DistributionGoods;
use app\plugins\distribution\models\DistributionGoodsDetail;
use app\plugins\distribution\models\DistributionLevel;
use app\plugins\distribution\models\DistributionSetting;
use app\plugins\distribution\models\RebuyLog;
use app\plugins\distribution\Plugin;
use yii\base\Component;
use yii\base\Exception;
use yii\queue\JobInterface;
use yii\queue\Queue;

class RebuyLogJob extends Component implements JobInterface
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
        \Yii::warning('分销复购队列开始执行');
        $order = CommonOrderDetail::findOne($this->common_order_detail_id);
        if (!$order) {
            \Yii::warning("---公共订单不存在：{$this->common_order_detail_id}  商城ID{$this->order->mall_id}---");
            return;
        }
        $this->order = $order;
        $mall = Mall::findOne($this->order->mall_id);
        if (!$mall) {
            \Yii::warning("---处理分销复购队列时候商城不存在公共订单ID：{$this->common_order_detail_id} 商城ID{$this->order->mall_id}---");
            return;
        }
        \Yii::$app->setMall($mall);


        \Yii::warning('当前的TYpe' . $this->type);

        $level = DistributionSetting::getValueByKey('level', $mall->id);
        if (!$level || $level == 0) {
            \Yii::warning("系统未开启分销");
            return;
        }
        $user = User::findOne($order->user_id);
        if (!$user) {
            \Yii::warning('分销订单找不到用户');
            return;
        }
        //1创建订单
        if ($this->type == 1) { //创建订单
            \Yii::warning('创建订单');
            $distribution = Distribution::findOne(['user_id' => $user->parent_id, 'is_delete' => 0]);
            if (!$distribution) {
                \Yii::warning('上级不是分销商');
                return;
            }

            //一级分销
            $log = new RebuyLog();
            $log->mall_id = $mall->id;
            $log->price = $order->price;
            $log->user_id = $distribution->user_id;
            $log->goods_type = $order->goods_type;
            $log->num = $order->num;
            $log->common_order_detail_id = $this->common_order_detail_id;
            $log->goods_id = $order->goods_id;
            if (!$log->save()){
                \Yii::warning(json_encode($log->getErrors()));
            }
            if ($level == 2) {
                $userParent = UserParent::findOne(['user_id' => $user->id, 'is_delete' => 0, 'level' => 2]);
                if ($userParent) {
                    $distribution = Distribution::findOne(['user_id' => $userParent->parent_id, 'is_delete' => 0]);
                    //一级分销
                    $log = new RebuyLog();
                    $log->mall_id = $mall->id;
                    $log->price = $order->price;
                    $log->user_id = $distribution->user_id;
                    $log->goods_type = $order->goods_type;
                    $log->common_order_detail_id = $this->common_order_detail_id;
                    $log->goods_id = $order->goods_id;
                    $log->num = $order->num;
                    $log->save();
                    if ($level == 3) {
                        $userParent = UserParent::findOne(['user_id' => $user->id, 'is_delete' => 0, 'level' => 3]);
                        if ($userParent) {
                            $distribution = Distribution::findOne(['user_id' => $userParent->parent_id, 'is_delete' => 0]);
                            //一级分销
                            $log = new RebuyLog();
                            $log->mall_id = $mall->id;
                            $log->price = $order->price;
                            $log->user_id = $distribution->user_id;
                            $log->goods_type = $order->goods_type;
                            $log->common_order_detail_id = $this->common_order_detail_id;
                            $log->goods_id = $order->goods_id;
                            $log->num = $order->num;
                            $log->save();
                        }
                    }
                }
            }
        }
        //这里是订单状态改变
        if ($this->type == 2) {
            //有效   更改当前的分销记录状态
            RebuyLog::updateAll(['status' => $order->status], ['common_order_detail_id' => $this->common_order_detail_id]);
        }
    }
}