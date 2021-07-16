<?php

namespace app\plugins\mch\forms\api;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Store;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchSubAccount;

class UserSubAccountMchListForm extends BaseModel{

    public $user_id;

    public function rules(){
        return [
            [['user_id'], 'required']
        ];
    }

    public function getList(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = MchSubAccount::find()->alias("msa");
            $query->innerJoin(["m" => Mch::tableName()], "m.id=msa.mch_id");
            $query->innerJoin(["s" => Store::tableName()], "s.mch_id=m.id");

            $query->where([
                "msa.user_id" => $this->user_id,
                "m.review_status" => Mch::REVIEW_STATUS_CHECKED,
                "m.is_delete" => 0
            ]);

            $query->select(["m.id", "s.name"]);

            $rows = $query->asArray()->orderBy("msa.id DESC")->all();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'mch_list' => $rows
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }
}