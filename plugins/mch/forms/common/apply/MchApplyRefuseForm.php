<?php
namespace app\plugins\mch\forms\common\apply;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\forms\api\MchApplyOperationLogSaveForm;
use app\plugins\mch\models\MchApply;
use app\plugins\mch\models\MchApplyOperationLog;

class MchApplyRefuseForm extends BaseModel
{

    public $id;
    public $remark;

    public function rules(){
        return [
            [['id'], 'required'],
            [['remark'], 'safe']
        ];
    }

    public function save($operation_terminal = MchApplyOperationLog::OPERATION_TERMINAL_BACKSTAGE){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        $trans = \Yii::$app->getDb()->beginTransaction();
        try {
            $applyModel = MchApply::findOne($this->id);
            if(!$applyModel){
                throw new \Exception("申请记录不存在",ApiCode::CODE_FAIL);
            }

            if($applyModel->status != "verifying"){
                throw new \Exception("非审核中状态无法操作",ApiCode::CODE_FAIL);
            }

            $applyModel->remark     = $this->remark;
            $applyModel->updated_at = time();
            $applyModel->status     = "refused";
            if(!$applyModel->save()){
                throw new \Exception($this->responseErrorMsg($applyModel),ApiCode::CODE_FAIL);
            }

            if ($operation_terminal == MchApplyOperationLog::OPERATION_TERMINAL_BACKSTAGE) {
                $user_id = \Yii::$app->admin->id;
            } else {
                $user_id = \Yii::$app->user->id;
            }
            $operation_save = MchApplyOperationLogSaveForm::addOperationLog($applyModel->mall_id, $applyModel->id, $operation_terminal, $user_id, MchApplyOperationLog::OPERATION_REFUSED);
            if ($operation_save['code'] != ApiCode::CODE_SUCCESS)
                throw new \Exception(isset($operation_save['msg']) ? $operation_save['msg'] : $operation_save['message'], ApiCode::CODE_FAIL);

            $trans->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            $trans->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}