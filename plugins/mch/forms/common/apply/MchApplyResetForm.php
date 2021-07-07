<?php
namespace app\plugins\mch\forms\common\apply;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\models\MchApply;

class MchApplyResetForm extends BaseModel{

    public $user_id;

    public function rules(){
        return [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
        ];
    }

    public function reset(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $applyModel = MchApply::findOne([
                "user_id" => $this->user_id
            ]);
            if(!$applyModel){
                throw new \Exception("无法获取到申请信息");
            }

            if($applyModel->status != "applying"){
                if($applyModel->status != "refused"){
                    throw new \Exception("审核中或已通过的入驻申请无法重置");
                }
                $applyModel->updated_at = time();
                $applyModel->status = "applying";
                if(!$applyModel->save()){
                    throw new \Exception($this->responseErrorMsg($applyModel));
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => '操作成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }

}