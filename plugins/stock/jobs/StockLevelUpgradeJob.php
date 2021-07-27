<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-22
 * Time: 14:21
 */

namespace app\plugins\stock\jobs;


use app\helpers\SerializeHelper;
use app\logic\CommonLogic;
use app\models\CommonOrder;
use app\models\CommonOrderDetail;
use app\models\UserChildren;
use app\models\UserGrowth;
use app\plugins\stock\models\FillOrder;
use app\plugins\stock\models\FillOrderDetail;
use app\plugins\stock\models\Stock;
use app\plugins\stock\models\StockAgent;
use app\plugins\stock\models\StockLevel;
use app\plugins\stock\models\StockOrder;
use app\plugins\stock\models\StockSetting;
use app\plugins\mch\models\Goods;
use yii\base\Component;
use yii\db\Exception;
use yii\queue\JobInterface;
use yii\queue\Queue;

class StockLevelUpgradeJob extends Component implements JobInterface
{
    public $user_id;
    public $mall_id;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */


    public function execute($queue)
    {
        // TODO: Implement execute() method.

        \Yii::warning("************************用户：{$this->user_id}升级队列开始执行******************************");
        $is_enable = StockSetting::getValueByKey(StockSetting::IS_ENABLE, $this->mall_id);
        if (!$is_enable) {
            \Yii::warning('系统没有开启云库存');
            return;
        }
        $agent = StockAgent::findOne(['mall_id' => $this->mall_id, 'user_id' => $this->user_id, 'is_delete' => 0]);
        if (!$agent) {
            \Yii::warning('不是代理商');
            return;
        } else {
            \Yii::warning('当前的代理商level' . $agent->level);
        }

        $level_list = StockLevel::find()->where(['mall_id' => $this->mall_id, 'is_use' => 1])->andWhere(['>', 'level', $agent->level])->orderBy('level desc')->all();
        \Yii::warning("AgentLevelUpgradeJob execute");
        if ($level_list) {
            $flag = false;
            $level_value = 0;
            $upgrade_status = 0;
            /**
             * @var StockLevel $level_list []
             * @var StockLevel $level
             */
            \Yii::warning('找到可以升级的等级');
            foreach ($level_list as $level) {
                \Yii::warning('当前的等级为：' . $level->level);
                $level_value = $level->level;
                if ($level->upgrade_type_condition) { //使用条件升级
                    $upgrade_status = StockAgent::UPGRADE_STATUS_CONDITION;
                    if (!$level->condition_type) {
                        break;
                    }
                    $checked_condition_values = $level->checked_condition_values;
                    $checked_condition_keys = $level->checked_condition_keys;
                    if (!$checked_condition_keys || !$checked_condition_values) {
                        break;
                    }
                    if ($checked_condition_keys == "" || $checked_condition_values == "") {
                        break;
                    }
                    $checked_condition_values = SerializeHelper::decode($checked_condition_values);
                    $checked_condition_keys = SerializeHelper::decode($checked_condition_keys);
                    //满足任意条件
                    if ($level->condition_type == 1) {

                        foreach ($checked_condition_keys as $key) {
                            if ($key == 0) {  //第一个条件
                                try{
                                    $is_upgrade = FillOrder::find()->alias('fo')->where(['fo.user_id' => $this->user_id])
                                        ->leftJoin(['d' => FillOrderDetail::tableName()], 'fo.id=d.order_id')
                                        ->andWhere(['d.goods_id' => $checked_condition_values[$key]['value']['val']])
                                        ->andWhere(['>=', 'd.num', intval($checked_condition_values[$key]['value']['val1'])])
                                        ->exists();
                                }catch (\Exception $e){
                                    \Yii::warning("StockLevelUpgradeJob error condition_type=1 ".CommonLogic::getExceptionMessage($e));
                                }
                                if ($is_upgrade) {
                                    $flag = true;
                                    break  2;
                                }
                            }
                            if ($key == 1) {
                                //第二个条件
                                $team_count = UserChildren::find()->alias('u')->where(['u.user_id' => $this->user_id])
                                    ->leftJoin(['a' => StockAgent::tableName()], 'a.user_id=u.child_id')
                                    ->andWhere(['a.is_delete' => 0, 'a.level' => intval($checked_condition_values[$key]['value']['val'])])
                                    ->count();
                                \Yii::warning('StockLevelUpgradeJob checked_condition_values[key]' . var_export($checked_condition_values[$key]['value'], true));
                                \Yii::warning('StockLevelUpgradeJob team_count=' . $team_count);
                                if ($team_count >= $checked_condition_values[$key]['value']['val1']) {
                                    $flag = true;
                                    break 2;
                                }
                            }
                        }
                    }
                    \Yii::warning('StockLevelUpgradeJob key' . var_export($checked_condition_keys, true));
                    //满足全部
                    if ($level->condition_type == 2) {
                        foreach ($checked_condition_keys as $key) {
                            $flag = false;
                            if ($key == 0) {  //第一个条件
                                $is_upgrade = FillOrder::find()->alias('fo')->where(['fo.user_id' => $this->user_id])
                                    ->leftJoin(['d' => FillOrderDetail::tableName()], 'fo.id=d.order_id')
                                    ->andWhere(['d.goods_id' => $checked_condition_values[$key]['value']['val']])
                                    ->andWhere(['>=', 'd.num', intval($checked_condition_values[$key]['value']['val1'])])
                                    ->exists();
                                if ($is_upgrade) {
                                    //完全满足条件
                                    \Yii::warning('StockLevelUpgradeJob 条件1达标');
                                    $flag = true;
                                } else {
                                    \Yii::warning('StockLevelUpgradeJob 条件1不达标');
                                    break 1;
                                }
                            }
                            if ($key == 1) {
                                $team_count = UserChildren::find()->alias('u')->where(['u.user_id' => $this->user_id])
                                    ->leftJoin(['a' => StockAgent::tableName()], 'a.user_id=u.child_id')
                                    ->andWhere(['a.is_delete' => 0, 'a.level' => intval($checked_condition_values[$key]['value']['val'])])
                                    ->count();
                                if ($team_count >= $checked_condition_values[$key]['value']['val1']) {
                                    $flag = true;
                                    continue;
                                } else {
                                    \Yii::warning('StockLevelUpgradeJob 条件2不达标');
                                    break 1;
                                }
                            }
                        }
                        if ($flag) {
                            break 1;
                        }
                    }
                }
            }

            if ($flag) {
                \Yii::warning("StockLevelUpgradeJob 用户：{$this->user_id}，代理商升级成功");
                $agent->level = $level_value;
                $agent->upgrade_status = $upgrade_status;
                $agent->upgrade_level_at = time();
                if (!$agent->save()) {
                    \Yii::error("StockLevelUpgradeJob 用户：{$this->user_id}，代理商升级失败，发生错误：" . var_export($agent->getErrors(), true));
                } else {
                    \Yii::warning("StockLevelUpgradeJob 用户：{$this->user_id}，代理商升级成功！");
                    return;
                }
            } else {
                \Yii::warning("StockLevelUpgradeJob 用户：{$this->user_id}，代理商升级{$level->level}失败，条件不满足");
            }
        }
    }
}