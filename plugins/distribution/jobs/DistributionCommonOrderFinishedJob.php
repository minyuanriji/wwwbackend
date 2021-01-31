<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-22
 * Time: 10:13
 */

namespace app\plugins\distribution\jobs;


use app\forms\common\distribution\DistributionCommon;
use app\models\CommonOrder;
use app\models\UserChildren;
use app\models\UserGrowth;
use app\models\UserParent;
use app\plugins\distribution\models\Distribution;
use app\plugins\distribution\models\DistributionSetting;
use yii\base\Component;
use yii\db\Exception;
use yii\queue\JobInterface;
use yii\queue\Queue;

class DistributionCommonOrderFinishedJob extends Component implements JobInterface
{
    public $common_order_id;
    public $status;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        \Yii::warning("DistributionCommonOrderFinishedJob execute");
        try{
            // TODO: Implement execute() method.
            $common_order = CommonOrder::findOne(['id' => $this->common_order_id, 'is_delete' => 0]);
            if (!$common_order) {
                return;
            }
            $distributionLevel = DistributionSetting::getValueByKey(DistributionSetting::LEVEL, $common_order->mall_id);
            if ($distributionLevel) {
                $userParentList = UserParent::find()->where(['user_id' => $common_order->user_id, 'is_delete' => 0, 'mall_id' => $common_order->mall_id])->andWhere(['<=', 'level', $distributionLevel])->all();
                \Yii::warning("DistributionCommonOrderFinishedJob execute distributionLevel={$distributionLevel} userParentList=".var_export($userParentList,true));
                if ($userParentList) {
                    \Yii::warning("DistributionCommonOrderFinishedJob foreach");
                    /**
                     * @var UserParent $userParentList [];
                     */
                    foreach ($userParentList as $item) {
                        $query = CommonOrder::find()->alias('co')
                            ->leftJoin(['uc' => UserChildren::tableName()], 'uc.child_id=co.user_id')
                            ->andWhere(['uc.user_id' => $item->parent_id])
                            ->andWhere(['<=', 'uc.level', $distributionLevel]);
                        $total_price = $query->sum('co.pay_price');
                        \Yii::warning("DistributionCommonOrderFinishedJob execute uc_user_id = {$item->parent_id} total_price=".$total_price);
                        if ($total_price) {
                            $growth = UserGrowth::findOne(['user_id' => $item->parent_id, 'keyword' => UserGrowth::KEY_DISTRIBUTION_ORDER_PRICE, 'is_delete' => 0, 'mall_id' => $common_order->mall_id]);
                            if (!$growth) {
                                $growth = new UserGrowth();
                                $growth->user_id = $item->parent_id;
                                $growth->mall_id = $common_order->mall_id;
                                $growth->keyword = UserGrowth::KEY_DISTRIBUTION_ORDER_PRICE;
                            }
                            $growth->value = $total_price;
                            $growth->save();
                        }
                        $total_count = $query->count();
                        \Yii::warning("DistributionCommonOrderFinishedJob execute uc_user_id = {$item->parent_id} total_count=".$total_count);
                        if ($total_count) {
                            $growth = UserGrowth::findOne(['user_id' => $item->parent_id, 'keyword' => UserGrowth::KEY_DISTRIBUTION_ORDER_COUNT, 'is_delete' => 0, 'mall_id' => $common_order->mall_id]);
                            if (!$growth) {
                                $growth = new UserGrowth();
                                $growth->user_id = $item->parent_id;
                                $growth->mall_id = $common_order->mall_id;
                                $growth->keyword = UserGrowth::KEY_DISTRIBUTION_ORDER_COUNT;
                            }
                            $growth->value = $total_count;
                            $growth->save();
                        }

                        $query = null;
                        $query = CommonOrder::find()->alias('co')
                            ->leftJoin(['uc' => UserChildren::tableName()], 'uc.child_id=co.user_id')
                            ->andWhere(['uc.user_id' => $item->parent_id])
                            ->andWhere(['<=', 'uc.level', 1]);
                        $total_price = $query->sum('co.pay_price');
                        \Yii::warning("DistributionCommonOrderFinishedJob execute uc_user_id = {$item->parent_id} total_price2=".$total_price);
                        if ($total_price) {
                            $growth = UserGrowth::findOne(['user_id' => $item->parent_id, 'keyword' => UserGrowth::KEY_DISTRIBUTION_FIRST_PRICE, 'is_delete' => 0, 'mall_id' => $common_order->mall_id]);
                            if (!$growth) {
                                $growth = new UserGrowth();
                                $growth->user_id = $item->parent_id;
                                $growth->mall_id = $common_order->mall_id;
                                $growth->keyword = UserGrowth::KEY_DISTRIBUTION_FIRST_PRICE;
                            }
                            $growth->value = $total_price;
                            $growth->save();
                        }
                        $total_count = $query->count();
                        \Yii::warning("DistributionCommonOrderFinishedJob execute uc_user_id = {$item->parent_id} total_count2=".$total_count);
                        if ($total_count) {
                            $growth = UserGrowth::findOne(['user_id' => $item->parent_id, 'keyword' => UserGrowth::KEY_DISTRIBUTION_FIRST_COUNT, 'is_delete' => 0, 'mall_id' => $common_order->mall_id]);
                            if (!$growth) {
                                $growth = new UserGrowth();
                                $growth->user_id = $item->parent_id;
                                $growth->mall_id = $common_order->mall_id;
                                $growth->keyword = UserGrowth::KEY_DISTRIBUTION_FIRST_COUNT;
                            }
                            $growth->value = $total_count;
                            $growth->save();
                        }
                    }
                }
            }
        }catch (Exception $ex){
            \Yii::error("DistributionCommonOrderFinishedJob error File:".$ex->getFile().";Line:".$ex->getLine().";message:".$ex->getMessage());
        }

    }
}