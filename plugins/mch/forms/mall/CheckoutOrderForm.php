<?php


namespace app\plugins\mch\forms\mall;


use app\core\ApiCode;
use app\plugins\mch\models\MchCheckoutOrder;
use app\plugins\sign_in\forms\BaseModel;

class CheckoutOrderForm extends BaseModel{

    public $mch_id;
    public $order_no;
    public $order_price;
    public $pay_price;
    public $is_pay;
    public $pay_user_id;
    public $pay_at;
    public $score_deduction_price;
    public $integral_deduction_price;
    public $created_at;
    public $updated_at;
    public $integral_fee_rate;

    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mall_id', 'mch_id', 'order_no', 'order_price', 'created_at', 'updated_at'], 'required'],
            [['is_pay', 'mch_id', 'mall_id', 'pay_user_id', 'pay_at', 'created_at', 'updated_at', 'is_delete'], 'integer'],
            [['order_price', 'pay_price', 'score_deduction_price', 'integral_deduction_price', 'integral_fee_rate'], 'number'],
            [['order_no'], 'string']
        ];
    }

    /**
     * 搜索
     * @return array|bool
     */
    public function search(){

        $query = MchCheckoutOrder::find();

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