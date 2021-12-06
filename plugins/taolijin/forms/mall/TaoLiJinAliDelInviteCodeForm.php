<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinAliInvitecode;

class TaoLiJinAliDelInviteCodeForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function delete(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $model = TaolijinAliInvitecode::findOne($this->id);
            if(!$model){
                throw new \Exception("邀请码不存在");
            }

            $model->is_delete = 1;
            if(!$model->save()){
                throw new \Exception("删除失败");
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '删除成功'
            ];

        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}