<?php
namespace app\commands;

use app\forms\common\UserIncomeForm;
use app\models\User;
use app\plugins\boss\forms\mall\BossAwardsEachLogForm;
use app\plugins\boss\forms\mall\BossAwardsSentLogForm;
use app\plugins\boss\models\Boss;
use app\plugins\boss\models\BossAwardEachLog;
use app\plugins\boss\models\BossAwardMember;
use app\plugins\boss\models\BossAwards;

class BossBonusController extends BaseCommandController
{
    public function actionMaintantJob()
    {
        $this->mutiKill();

        echo date("Y-m-d H:i:s") . " 分红开始...\n";

        $this->bonus();
die;
        while(true){
            $this->sleep(1);
            try {
                //分红
                $this->bonus();

            }catch (\Exception $e){
                $this->commandOut($e->getMessage());
            }
        }
    }

    /**
     * @return number
     */
    private function computingTime($type){
        switch ($type)
        {
            case 'day':
                $second = 24 * 60 * 60;
                break;
            case 'week':
                $second = 7 * 24 * 60 * 60;
                break;
            case 'month':
                $second = date("t") * 24 * 60 * 60;
                break;
            case 'year':
                $second = 365 * 24 * 60 * 60;
                break;
            default:
                $second = 0;
                break;
        }
        return $second;
    }

    /**
     * 分红
     * @return boolean
     */
    private function bonus ()
    {
        try {
            //查看奖金池
            $query = BossAwards::find();
            $query->andWhere([
                "AND",
                ["status" => 1],
                ["is_delete" => 0],
            ]);
            $boss_awards_data = $query->asArray()->all();
            if(!$boss_awards_data){
                $this->commandOut("暂无分红奖金池");
                return false;
            }

            $each_log_form = new BossAwardsEachLogForm();
            $sent_log_form = new BossAwardsSentLogForm();
            $boss_awards_model = new BossAwards();
            $time = time();
            $trans = \Yii::$app->db->beginTransaction();
            $each_log_data = [];
            foreach ($boss_awards_data as $awards_key => $awards_value)
            {
                $next_time[$awards_key] = date('Ymd', $awards_value['next_send_time']);

                if ($next_time[$awards_key] != date('Ymd', $time)) {
                    $this->commandOut($awards_value['name'] . "奖金池未到发放时间");
                    continue;
                }
                //查看当前奖池是否已经发放
                $new_award = BossAwardEachLog::find()
                                ->andWhere(["awards_id" => $awards_value['id']])
                                ->orderBy('id desc')
                                ->asArray()
                                ->one();
                if ($new_award && $new_award['sent_time'] == $next_time[$awards_key]) {
                    $this->commandOut($awards_value['name'] . "奖金池已发放");
                    continue;
                }

                if ($awards_value['money'] <= 0) {
                    $this->commandOut($awards_value['name'] . "奖金池金额不足：" . date("Y-m-d H:i:s", $time));
                    continue;
                }

                //查询当前奖池需要发放人员
                $boss_data = [];
                if ($awards_value['level_id']) {
                    $boss_data = Boss::find()
                        ->select('id,user_id,level_id')
                        ->andWhere(['level_id' => $awards_value['level_id'], 'is_delete' => 0])
                        ->asArray()
                        ->all();
                }

                $awards_member_data = BossAwardMember::find()
                    ->select('user_id')
                    ->andWhere(['award_id' => $awards_value['id']])
                    ->asArray()
                    ->all();
                if (!$awards_member_data && !$awards_value['level_id']) {
                    $this->commandOut($awards_value['name'] . "奖金池暂无分红股东");
                    continue;
                }

                $awards_member_data = array_merge_recursive($awards_member_data, $boss_data);

                $user_ids = array_unique(array_column($awards_member_data, 'user_id'));

                $price[$awards_key] = $awards_value['money'] * ($awards_value['rate'] * 0.01);//计算分红金额

                $count_user[$awards_key] = count($user_ids);//分红总人数

                $per_person[$awards_key] = $price[$awards_key] / $count_user[$awards_key];//每人分的钱

                try {
                    //查看是否是自动分红
                    if ($awards_value['automatic_audit']) {
                        //修改奖池金额
                        $award_res = $boss_awards_model->updateAll(['money' => $awards_value['money'] - $price[$awards_key]],['id' => $awards_value['id']]);
                        if (!$award_res) {
                            $trans->rollBack();
                            $this->commandOut($awards_value['name'] . "修改奖池金额失败");
                            continue;
                        }

                        $each_log_data[$awards_key]['actual_money'] = $price[$awards_key];
                        $each_log_data[$awards_key]['money_after'] = $awards_value['money'] - $price[$awards_key];
                    } else {
                        $each_log_data[$awards_key]['actual_money'] = 0;
                        $each_log_data[$awards_key]['money_after'] = $awards_value['money'];
                    }
                    //添加每期奖池记录
                    $each_log_data[$awards_key] = array_merge($each_log_data[$awards_key],[
                        "awards_cycle"      => $awards_value['name'] . "第" . $next_time[$awards_key] . '期',
                        "awards_id"         => $awards_value['id'],
                        "money"             => $price[$awards_key],
                        "people_num"        => $count_user[$awards_key],
                        "money_front"       => $awards_value['money'],
                        "rate"              => $awards_value['rate'],
                        "sent_time"         => $next_time[$awards_key],
                    ]);
                    $each_res = $each_log_form->save($each_log_data[$awards_key]);
                    if (!isset($each_res['code']) || $each_res['code']) {
                        $trans->rollBack();
                        $this->commandOut($awards_value['name'] . "奖金池添加每期记录失败");
                        continue;
                    }

                    //添加每人每期发放记录
                    foreach ($awards_member_data as $sent_key => $sent_val)
                    {
                        $sent_log_data = [
                            "each_id"       => $each_res['data'],
                            "user_id"       => $sent_val['user_id'],
                            "money"         => $per_person[$awards_key],
                            "award_set"     => json_encode([
                                    'money'         => $price[$awards_key],
                                    'rate'          => $awards_value['rate'],
                                    'people_number' => $count_user[$awards_key],
                            ]),
                            "send_date"     => $next_time[$awards_key],
                        ];
                        if ($awards_value['automatic_audit']) {
                            $sent_log_data['status'] = 1;
                            $sent_log_data['payment_time'] = $time;
                        }
                        $sent_res = $sent_log_form->save($sent_log_data);

                        if (!isset($sent_res['code']) || $sent_res['code']) {
                            $trans->rollBack();
                            $this->commandOut($awards_value['name'] . "奖金池". $sent_val['user_id'] ."用户添加记录失败");
                            continue;
                        }

                        if ($awards_value['automatic_audit']) {
                            //修改用户金额
                            $user = User::findOne((int)$sent_val['user_id']);
                            if (!$user || $user->is_delete) {
                                $trans->rollBack();
                                $this->commandOut($awards_value['name'] . "奖金池" . $sent_val['user_id'] . "用户添加记录失败");
                                continue;
                            }
                            UserIncomeForm::bossAdd($user, $per_person[$awards_key], $sent_res['data'],'来自股东分红' . $awards_value['name'] . "第" . $next_time[$awards_key] . '期');

                            //修改股东总分红记录
                            $boss = Boss::findOne(['user_id' => $sent_val['user_id'], 'is_delete' => 0]);
                            if(!$boss){
                                $trans->rollBack();
                                $this->commandOut($sent_val['user_id'].'股东不存在');
                                continue;
                            }
                            $boss->total_price = $boss->total_price + $per_person[$awards_key];
                            if (!$boss->save()) {
                                $trans->rollBack();
                                $this->commandOut($awards_value['name'] . "奖金池" . $sent_val['user_id'] . "修改股东总分红记录失败");
                                continue;
                            }
                        }
                    }

                    //修改当前奖池日期
                    BossAwards::updateAll([
                        'last_send_time' => $awards_value['next_send_time'],
                        'next_send_time' => $awards_value['next_send_time'] + ($awards_value['period'] * $this->computingTime($awards_value['period_unit']))
                    ],
                        'id = ' . $awards_value['id']);

                    $this->commandOut($awards_value['name'] . "奖金池发放完成");
                } catch (\Exception $e){
                    $trans->rollBack();
                    $this->commandOut($e->getMessage());
                }
            }

            $trans->commit();
            $this->commandOut(date('Y-m-d H:i:s', time()) . " 分红结束...");
            return true;
        } catch (\Exception $e){
            $this->commandOut($e->getMessage());
        }
    }
}
