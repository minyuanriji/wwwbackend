<?php
namespace app\plugins\hotel\forms\mall;


use app\core\ApiCode;
use app\models\User;
use app\plugins\hotel\helpers\OrderHelper;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\hotel\models\HotelRefundApplyOrder;
use app\plugins\hotel\models\HotelRoom;
use app\plugins\hotel\models\Hotels;

class HotelOrderRefundListForm extends HotelOrderListForm{

    public $status;
    public $keyword;

    public function rules(){
        return [
            [['status', 'keyword'], 'string']
        ];
    }

    public function getList(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = HotelRefundApplyOrder::find()->alias("rfo");
            $query->innerJoin(["o" => HotelOrder::tableName()], "o.id=rfo.order_id");
            $query->innerJoin(["ho" => Hotels::tableName()], "ho.id=o.hotel_id");
            $query->innerJoin(["r" => HotelRoom::tableName()], "r.product_code=o.product_code");
            $query->innerJoin(["u" => User::tableName()], "u.id=o.user_id");

            if(!empty($this->keyword)){
                $query->andWhere([
                    "OR",
                    ["LIKE", "ho.name", $this->keyword],
                    ["LIKE", "o.order_no", $this->keyword],
                    ["LIKE", "u.nickname", $this->keyword],
                    ["u.mobile" => $this->keyword],
                    ["u.id" => $this->keyword]
                ]);
            }

            $this->statusFilter($query);

            $query->orderBy("o.id DESC");

            $selects = [
                "u.id as user_id", "u.nickname", "ho.name as hotel_name",
                "o.order_status", "o.pay_status", "o.booking_num", "o.integral_deduction_price",
                "o.booking_start_date", "o.order_no", "o.order_price", "o.booking_days",
                "o.pay_at", "o.pay_price",  "o.booking_arrive_date", "o.created_at", "o.updated_at",
                "o.booking_passengers",
                "rfo.id", "rfo.status as refund_status", "rfo.refund_price", "rfo.remark as refund_remark"];

            $list = $query->select($selects)->page($pagination, 20)->asArray()->all();
            foreach($list as &$row){
                $statusInfo = OrderHelper::getOrderRealStatus(
                    $row['order_status'], $row['pay_status'], $row['created_at'],
                    $row['booking_start_date'], $row['booking_days']
                );
                $row['real_status']   = $statusInfo['status'];
                $row['status_text']   = $statusInfo['text'];
                $row['passengers']    = !empty($row['booking_passengers']) ? json_decode($row['booking_passengers']) : [];
                $row['end_date']      = date("Y-m-d", strtotime($row['booking_start_date']) + $row['booking_days'] * 3600 * 24);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'pagination' => $pagination
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