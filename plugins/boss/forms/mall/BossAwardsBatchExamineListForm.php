<?php

namespace app\plugins\boss\forms\mall;

use app\core\ApiCode;
use app\forms\common\UserIncomeForm;
use app\models\BaseModel;
use app\models\User;
use app\plugins\boss\models\Boss;
use app\plugins\boss\models\BossAwardEachLog;
use app\plugins\boss\models\BossAwards;
use app\plugins\boss\models\BossAwardSentLog;

class BossAwardsBatchExamineListForm extends BaseModel
{
    public $ids;

    public function rules()
    {
        return [
            [['ids'], 'safe']
        ];
    }

    //审核
    public function examine()
    {
        $ids = $this->ids;

        $t = \Yii::$app->db->beginTransaction();
        try {
            //修改状态
            foreach ($ids as $item) {
                $sent = BossAwardSentLog::findOne(['id' => $item, 'status' => 0]);
                if (!$sent)
                    throw new \Exception('奖金需要发送记录不存在！ID: ' . $item);

                $sent->status = 1;
                $sent->payment_time = time();
                if (!$sent->save())
                    throw new \Exception($sent->getErrorMessage());

                $each_log = new BossAwardsEachLogForm();

                //修改该期奖池金额
                $each_res = BossAwardEachLog::find()->andWhere(['id' => $sent->each_id])->one();
                if (!$each_res)
                    throw new \Exception('该期奖池金额不存在');

                $each_log->save([
                    'id' => $sent->each_id,
                    'actual_money' => $each_res->actual_money + $sent->money,
                    'money_after' => $each_res->money_after - $sent->money,
                ]);

                //修改奖池金额
                $awards_res = BossAwards::find()->andWhere(['AND',['is_delete' => 0],['id' => $each_res->awards_id]])->one();
                if (!$awards_res)
                    throw new \Exception('奖池金额不存在');

                $awards_res->money = $awards_res->money - $sent->money;
                if (!$awards_res->save())
                    throw new \Exception($awards_res->getErrorMessage());

                //修改用户金额
                $user = User::findOne($sent->user_id);
                if(!$user || $user->is_delete)
                    throw new \Exception("用户不存在");

                UserIncomeForm::bossAdd($user, $sent->money, $item);

                //修改股东总分红记录
                $boss = Boss::findOne(['user_id' => $sent->user_id, 'is_delete' => 0]);
                if(!$boss)
                    throw new \Exception("股东不存在");

                $boss->total_price = $boss->total_price + $sent->money;
                if (!$boss->save())
                    throw new \Exception($boss->getErrorMessage());

            }

            $t->commit();
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '批量打款成功');
        } catch (\Exception $exception) {
            $t->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $exception->getMessage());
        }
    }
}