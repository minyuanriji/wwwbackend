<?php
namespace app\mch\forms\order;

use app\models\BaseModel;
use app\models\Integral;
use app\models\IntegralRecord;
use app\models\User;

class CheckoutOrderDeductIntegralForm extends BaseModel{

    public $user_id;  //用户ID
    public $deduction_price; //抵扣金额
    public $desc;

    public $source_table;//来源表
    public $source_id;   //来源ID

    public static $errorMsg;

    public function rules(){
        return [
            [['user_id', 'deduction_price', 'source_table', 'source_id'], 'required'],
            [['user_id', 'source_id'], 'integer'],
            [["deduction_price"], "number", "min" => 0],
            [['source_table', 'desc'], 'string']
        ];
    }

    /**
     * 金豆券抵扣
     * @return boolean
     */
    public function save(){

        $t = \Yii::$app->db->beginTransaction();
        try {
            if (!$this->validate()) {
                throw new \Exception($this->responseErrorMsg());
            }

            if((float)$this->deduction_price <= 0){
                throw new \Exception("抵扣金额必须大于0");
            }

            //需要抵扣的金豆券数
            $integralDeductionPrice = (float)$this->deduction_price ;

            //用户对象
            $user = User::findOne($this->user_id);
            $beforeMoney = $user->static_integral;

            $desc = $this->desc . '（永久金豆券抵扣）';

            $record = array(
                'controller_type' => 1,
                'mall_id'         => $user->mall_id,
                'user_id'         => $user->id,
                'money'           => $integralDeductionPrice * -1,
                'desc'            => $desc,
                'before_money'    => $beforeMoney,
                'type'            => Integral::TYPE_ALWAYS,
                'source_id'       => $this->source_id,
                'source_table'    => $this->source_table,
            );
            // 写入日志
            if(!IntegralRecord::record($record)){
                throw new \Exception(IntegralRecord::getError());
            }

            $t->commit();
        }catch (\Exception $e){
            $t->rollBack();
            static::$errorMsg = $e->getMessage();
            return false;
        }

        return true;
    }
}