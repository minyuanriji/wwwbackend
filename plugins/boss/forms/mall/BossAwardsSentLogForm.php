<?php

namespace app\plugins\boss\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\boss\models\BossAwardSentLog;

class BossAwardsSentLogForm extends BaseModel
{
    //添加记录
    public function save($data)
    {
        try {
            $sent_log_model = new BossAwardSentLog();
            $sent_log_model->each_id = $data['each_id'];
            $sent_log_model->user_id = $data['user_id'];
            $sent_log_model->money = $data['money'];
            $sent_log_model->award_set = $data['award_set'];
            $sent_log_model->send_date = $data['send_date'];
            if (!$sent_log_model->save()) {
                throw new \Exception($this->responseErrorMsg($sent_log_model));
            } else {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功'
                ];
            }
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }

}