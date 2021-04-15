<?php
namespace app\forms\api\order;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\OrderClerk;

class QueryClerkStatusForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'integer'],
            [['id'], 'required']
        ];
    }

    public function queryClerk(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $exists = OrderClerk::find()->where([
            "order_id"  => $this->id,
            "is_delete" => 0
        ])->exists();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                  'clerk_status' => $exists ? 1 : 0
            ],
            'msg' => '查询成功',
        ];

    }
}