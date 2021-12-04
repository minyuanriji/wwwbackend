<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinAli;
use app\plugins\taolijin\models\TaolijinAliInvitecode;

class TaoLiJinAliInviteCodeListForm extends BaseModel{

    public $ali_id;

    public function rules(){
        return [
            [['ali_id'], 'required']
        ];
    }

    public function getList(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $aliModel = TaolijinAli::findOne($this->ali_id);
            if(!$aliModel || $aliModel->is_delete){
                throw new \Exception("联盟数据不存在");
            }


            $list = TaolijinAliInvitecode::find()->where([
                "ali_id"    => $aliModel->id,
                "is_delete" => 0
            ])->asArray()->all();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list ? $list : []
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}