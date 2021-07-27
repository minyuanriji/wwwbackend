<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-22
 * Time: 10:13
 */

namespace app\plugins\boss\jobs;


use app\forms\common\boss\BossCommon;
use app\models\CommonOrder;
use app\models\UserChildren;
use app\models\UserGrowth;
use app\models\UserParent;
use app\plugins\boss\models\Boss;
use app\plugins\boss\models\BossSetting;
use yii\base\Component;
use yii\db\Exception;
use yii\queue\JobInterface;
use yii\queue\Queue;

class BossCommonOrderFinishedJob extends Component implements JobInterface
{
    public $common_order_id;
    public $status;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        \Yii::warning("BossCommonOrderFinishedJob execute");
        try{
            // TODO: Implement execute() method.
            $common_order = CommonOrder::findOne(['id' => $this->common_order_id, 'is_delete' => 0]);
            if (!$common_order) {
                return;
            }
            $bossLevel = BossSetting::getValueByKey(BossSetting::LEVEL, $common_order->mall_id);
            if ($bossLevel) {
                $userParentList = UserParent::find()->where(['user_id' => $common_order->user_id, 'is_delete' => 0, 'mall_id' => $common_order->mall_id])->andWhere(['<=', 'level', $bossLevel])->all();
                \Yii::warning("BossCommonOrderFinishedJob execute bossLevel={$bossLevel} userParentList=".var_export($userParentList,true));
                if ($userParentList) {
                    \Yii::warning("BossCommonOrderFinishedJob foreach");
                    /**
                     * @var UserParent $userParentList [];
                     */
                    foreach ($userParentList as $item) {
                        $query = CommonOrder::find()->alias('co')
                            ->leftJoin(['uc' => UserChildren::tableName()], 'uc.child_id=co.user_id')
                            ->andWhere(['uc.user_id' => $item->parent_id])
                            ->andWhere(['<=', 'uc.level', $bossLevel]);
                        $total_price = $query->sum('co.pay_price');
                        \Yii::warning("BossCommonOrderFinishedJob execute uc_user_id = {$item->parent_id} total_price=".$total_price);
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
                        \Yii::warning("BossCommonOrderFinishedJob execute uc_user_id = {$item->parent_id} total_count=".$total_count);
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
                        \Yii::warning("BossCommonOrderFinishedJob execute uc_user_id = {$item->parent_id} total_price2=".$total_price);
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
                        \Yii::warning("BossCommonOrderFinishedJob execute uc_user_id = {$item->parent_id} total_count2=".$total_count);
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
            \Yii::error("BossCommonOrderFinishedJob error File:".$ex->getFile().";Line:".$ex->getLine().";message:".$ex->getMessage());
        }

    }
}