<?php

namespace app\plugins\oil\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\oil\models\OilOrders;

class OilOrderListForm extends BaseModel {

    public $page;

    public function rules(){
        return [
            [['page'], 'integer']
        ];
    }

    public function getList(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = OilOrders::find()->where([
                "user_id" => \Yii::$app->user->id
            ]);

            $selects = ["id", "product_id", "order_no", "order_status", "order_price", "created_at", "updated_at",
                "pay_status", "pay_at", "pay_price", "pay_type", "integral_deduction_price", "integral_fee_rate"
            ];
            $list = $query->select($selects)->page($pagination, 10, max(1, (int)$this->page))->orderBy("id DESC")->asArray()->all();
            if($list){
                foreach($list as &$item){
                    $statusInfo = OilOrders::getStatusInfo($item['order_status'], $item['pay_status'], $item['created_at']);
                    $item['status_text']  = $statusInfo['text'];
                    $item['order_status'] = $statusInfo['status'];
                    $item['created_at']   = date("Y-m-d H:i:s", $item['created_at']);
                    $item['updated_at']   = date("Y-m-d H:i:s", $item['updated_at']);
                }
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                'list'       => $list ?: [],
                'pagination' => $pagination
            ]);
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }

}