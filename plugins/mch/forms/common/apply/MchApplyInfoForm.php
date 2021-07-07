<?php

namespace app\plugins\mch\forms\common\apply;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\models\MchApply;

class MchApplyInfoForm extends BaseModel{

    public $user_id;

    public function rules(){
        return [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
        ];
    }

    public function get(){
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

            $info['realname']   = $applyModel->realname;
            $info['mobile']     = $applyModel->realname;
            $info['status']     = $applyModel->status;
            $info['apply_time'] = date("Y-m-d H:i:s", $applyModel->created_at);
            $info['update_time'] = date("Y-m-d H:i:s", $applyModel->updated_at);

            $info = array_merge($info, json_decode($applyModel->json_apply_data, true));


            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $info
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }
}