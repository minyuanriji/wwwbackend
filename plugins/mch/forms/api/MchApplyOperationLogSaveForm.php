<?php

namespace app\plugins\mch\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\models\MchApplyOperationLog;

class MchApplyOperationLogSaveForm extends BaseModel
{
    public static function addOperationLog ($mall_id, $mch_apply_id, $operation_terminal, $user_id, $operation)
    {
        try {
            $mac_apply_operation_model = new MchApplyOperationLog();
            $mac_apply_operation_model->mall_id             = $mall_id;
            $mac_apply_operation_model->mch_apply_id        = $mch_apply_id;
            $mac_apply_operation_model->user_id             = $user_id;
            $mac_apply_operation_model->operation_terminal  = $operation_terminal;
            $mac_apply_operation_model->operation           = $operation;
            $mac_apply_operation_model->created_at          = time();
            if (!$mac_apply_operation_model->save()) {
                throw new \Exception(json_encode($mac_apply_operation_model->getErrors()), ApiCode::CODE_FAIL);
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
