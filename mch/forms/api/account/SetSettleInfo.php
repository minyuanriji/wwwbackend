<?php


namespace app\mch\forms\api\account;


use app\core\ApiCode;
use app\models\BaseModel;

class SetSettleInfo extends BaseModel{

    public $mch_id;
    public $paper_settleAccountType;
    public $paper_settleAccountNo;
    public $paper_settleAccount;
    public $paper_settleTarget;
    public $paper_openBank;

    public function rules(){
        return array_merge(parent::rules(), [
            [['mch_id'], 'required']
        ]);
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => "操作成功",
                'data' => []
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage(),
            ];
        }
    }
}