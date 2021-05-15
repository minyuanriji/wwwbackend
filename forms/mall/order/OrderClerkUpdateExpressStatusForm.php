<?php
namespace app\forms\mall\order;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\OrderClerk;

class OrderClerkUpdateExpressStatusForm extends BaseModel{

    public $id;
    public $express_status;

    public function rules(){
        return [
            [['id', 'express_status'], 'required'],
            [['id', 'express_status'], 'integer']
        ];
    }

    public function update(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $orderClerk = OrderClerk::findOne($this->id);
            if(!$orderClerk || $orderClerk->is_delete){
                throw new \Exception("无法获取核销记录");
            }

            $orderClerk->express_status = $this->express_status;
            $orderClerk->updated_at = time();
            if(!$orderClerk->save()){
                throw new \Exception($this->responseErrorMsg($orderClerk));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '保存成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}