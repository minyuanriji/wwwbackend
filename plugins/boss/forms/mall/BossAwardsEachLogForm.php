<?php

namespace app\plugins\boss\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\boss\models\BossAwardEachLog;

class BossAwardsEachLogForm extends BaseModel
{
    //添加记录
    public function save($data)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            if (isset($data['id'])) {
                $each_res = BossAwardEachLog::find()->andWhere(['id' => $data['id']])->one();
                if (!$each_res) {
                    throw new \Exception($this->responseErrorMsg($each_res));
                }
            } else {
                $each_res = new BossAwardEachLog();
                $each_res->awards_cycle   = $data['awards_cycle'];
                $each_res->awards_id      = $data['awards_id'];
                $each_res->money          = $data['money'];
                $each_res->people_num     = $data['people_num'];
                $each_res->money_front    = $data['money_front'];
                $each_res->rate           = $data['rate'];
                $each_res->sent_time      = $data['sent_time'];
            }
            $each_res->actual_money = $data['actual_money'];
            $each_res->money_after    = $data['money_after'];
            if (!$each_res->save()) {
                throw new \Exception($this->responseErrorMsg($each_res));
            } else {
                $t->commit();
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功',
                    'data' => $each_res->id,
                ];
            }
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }
}