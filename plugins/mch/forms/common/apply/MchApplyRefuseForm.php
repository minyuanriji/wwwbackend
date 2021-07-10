<?php
namespace app\plugins\mch\forms\common\apply;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\models\MchApply;

class MchApplyRefuseForm extends BaseModel{

    public $id;
    public $remark;

    public function rules(){
        return [
            [['id'], 'required'],
            [['remark'], 'safe']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $applyModel = MchApply::findOne($this->id);
            if(!$applyModel){
                throw new \Exception("申请记录不存在");
            }

            if($applyModel->status != "verifying"){
                throw new \Exception("非审核中状态无法操作");
            }

            $applyModel->remark     = $this->remark;
            $applyModel->updated_at = time();
            $applyModel->status     = "refused";
            if(!$applyModel->save()){
                throw new \Exception($this->responseErrorMsg($applyModel));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}