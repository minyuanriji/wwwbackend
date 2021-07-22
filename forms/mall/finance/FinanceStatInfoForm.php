<?php

namespace app\forms\mall\finance;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Cash;
use app\models\IncomeLog;
use app\models\Integral;
use app\models\Order;
use app\models\User;
use app\plugins\mch\models\MchCheckoutOrder;

class FinanceStatInfoForm extends BaseModel{

    public $user_id;

    public function rules(){
        return [
            [['user_id'], 'safe']
        ];
    }

    public function get(){
        try {

            $user = null;
            if($this->user_id){
                $user = User::findOne($this->user_id);
                if(!$user){
                    throw new \Exception("用户不存在");
                }
            }

            $query = function(){
                $query = Order::find()->andWhere([
                    "AND",
                    ["is_pay" => 1],
                    ["is_delete" => 0],
                    ["is_recycle" => 0],
                    ["IN", "status", [1,2,3,4,6,7,8]]
                ]);
                if($this->user_id){
                    $query->andWhere(["user_id" => $this->user_id]);
                }
                return $query;
            };

            //商品消费总额
            $data['total_goods_paid'] = (float)$query()->sum("total_goods_original_price");

            //商品消费红包抵扣总额
            $data['total_goods_integral_paid'] = (float)$query()->sum("integral_deduction_price");

            $query = function(){
                $query = MchCheckoutOrder::find()->where([
                    "is_pay" => 1,
                    "is_delete" => 0
                ]);
                if($this->user_id){
                    $query->andWhere(["pay_user_id" => $this->user_id]);
                }
                return $query;
            };

            //店铺消费总额
            $data['total_checkout_paid'] = (float)$query()->sum("order_price");

            //店铺红包抵扣总额
            $data['total_checkout_integral_paid'] = (float)$query()->sum("integral_deduction_price");

            //消费总额
            $data['total_paid'] =  $data['total_goods_paid'] + $data['total_checkout_paid'];

            //红包抵扣总额
            $data['total_integral_paid'] = $data['total_goods_integral_paid'] + $data['total_checkout_integral_paid'];

            //红包获得总数
            $query = Integral::find()->where(["controller_type" => 1]);
            if($this->user_id){
                $query->andWhere(["user_id" => $this->user_id]);
            }
            $data['total_integral_got'] = (float)$query->sum("integral_num * period");

            //总收益
            $query = IncomeLog::find()->where([
                "user_id" => (int)$this->user_id,
                "type" => 1,
                "is_delete" => 0]);
            $data['total_income'] = (float)$query->sum("income");

            //提现总额/总笔数
            $query = Cash::find()->where([
                "status" => 2,
                "is_delete" => 0,
                "user_id" => (int)$this->user_id
            ]);

            $data['total_cash'] = (float)$query->sum("price");
            $data['total_cash_count'] = (int)$query->count();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'stat_info' => $data,
                    'nickname'  => $user ? $user->nickname : '',
                    'user_id'   => $user ? $user->id : 0
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