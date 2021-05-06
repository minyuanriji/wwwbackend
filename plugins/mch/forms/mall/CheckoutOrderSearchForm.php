<?php


namespace app\plugins\mch\forms\mall;


use app\core\ApiCode;
use app\plugins\mch\models\MchCheckoutOrder;
use app\plugins\sign_in\forms\BaseModel;

class CheckoutOrderSearchForm extends BaseModel{

    public $keyword;
    public $pay_status;

    public function rules(){
        return array_merge(parent::rules(), [
            [["keyword", "pay_status"], "string"]
        ]);
    }

    /**
     * 搜索
     * @return array|bool
     */
    public function search(){

        $query = MchCheckoutOrder::find();

        if(!empty($this->keyword)){
            $query->andWhere(["LIKE", "order_no", $this->keyword]);
        }

        if(!empty($this->pay_status)){
            if($this->pay_status == "paid"){ //已支付
                $query->andWhere(["is_pay" => 1]);
            }
            if($this->pay_status == "unpaid"){ //未支付
                $query->andWhere(["is_pay" => 0]);
            }
        }

        $rows = $query->page($pagination)
                      ->with(["mchStore", "payUser"])
                      ->orderBy("id DESC")
                      ->asArray()
                      ->all();
        $list = [];
        if($rows){
            foreach($rows as $row){
                if(empty($row['mchStore']) || empty($row['payUser']))
                    continue;
                $row['format_pay_time'] = "";
                if($row['is_pay']){
                    $row['format_pay_time'] = date("Y-m-d H:i:s", $row['pay_at']);
                }
                $list[] = $row;
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'pagination' => $pagination,
                'list'       => $list
            ]
        ];
    }
}