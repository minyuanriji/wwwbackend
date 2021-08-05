<?php
namespace app\plugins\finance_analysis\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Cash;
use app\models\EfpsPaymentOrder;
use app\plugins\mch\models\MchCash;

class FinanceIncomeStatForm extends BaseModel{

    public $date_start;
    public $date_end;
    public $time_start;

    public function rules(){
        return [
            [['date_start', 'date_end', 'time_start'], 'safe']
        ];
    }

    public function get(){

        try {
            $timeStart = time() - ($this->time_start ? (int)$this->time_start : 0);
            $dayStartTime = strtotime($this->date_start);
            $dayEndTime = strtotime($this->date_end);

            //收入
            $query = EfpsPaymentOrder::find()->where([
                "is_pay" => 1
            ]);
            if(!empty($this->date_start) && !empty($this->date_end)){
                $query->andWhere([
                    "AND",
                    [">", "update_at", $dayStartTime],
                    ["<", "update_at", $dayEndTime]
                ]);
            }else{
                $query->andWhere([">", "update_at", $timeStart]);
            }
            $totalIncome = (float)$query->sum("payAmount");
            $totalIncome = round($totalIncome/100, 2);

            //支出包括商户、用户的提现
            $totalDisburse = 0;

            //用户提现
            $query = Cash::find()->where(["type" => "bank", "status" => 2, "is_delete" => 0]);
            if(!empty($this->date_start) && !empty($this->date_end)){
                $query->andWhere([
                    "AND",
                    [">", "updated_at", $dayStartTime],
                    ["<", "updated_at", $dayEndTime]
                ]);
            }else{
                $query->andWhere([">", "updated_at", $timeStart]);
            }
            $totalDisburse += (float)$query->sum("fact_price");

            //商户提现
            $query = MchCash::find()->where([
                "status"          => 1,
                "transfer_status" => 1,
                "is_delete"       => 0,
                "type"            => "efps_bank"
            ]);
            if(!empty($this->date_start) && !empty($this->date_end)){
                $query->andWhere([
                    "AND",
                    [">", "updated_at", $dayStartTime],
                    ["<", "updated_at", $dayEndTime]
                ]);
            }else{
                $query->andWhere([">", "updated_at", $timeStart]);
            }
            $totalDisburse += (float)$query->sum("fact_price");

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    "total_income"   => $totalIncome,
                    "total_disburse" => $totalDisburse
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ];
        }
    }

}