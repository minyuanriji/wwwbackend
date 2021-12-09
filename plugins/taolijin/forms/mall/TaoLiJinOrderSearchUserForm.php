<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\taolijin\models\TaolijinAli;
use app\plugins\taolijin\models\TaolijinUserAliBind;

class TaoLiJinOrderSearchUserForm extends BaseModel{

    public $ali_id;
    public $special_id;

    public function rules(){
        return array_merge(parent::rules(), [
            [['ali_id'], 'required'],
            [['special_id'], 'safe']
        ]);
    }

    public function search(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $aliModel = TaolijinAli::findOne($this->ali_id);
            if(!$aliModel || $aliModel->is_delete){
                throw new \Exception("联盟不存在");
            }

            $user = null;
            if($aliModel->ali_type == "ali"){
                $aliBind = TaolijinUserAliBind::findOne(["ali_id" => $aliModel->id, "special_id" => $this->special_id]);
                $user = $aliBind ? $aliBind->user : null;
            }else{

            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    "user" => $user ? $user->getAttributes() : ''
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