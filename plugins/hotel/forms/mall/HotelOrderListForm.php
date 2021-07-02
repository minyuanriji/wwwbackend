<?php
namespace app\plugins\hotel\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\hotel\helpers\OrderHelper;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\hotel\models\HotelRoom;
use app\plugins\hotel\models\Hotels;
use yii\db\ActiveQuery;

class HotelOrderListForm extends BaseModel{

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

            $query = HotelOrder::find()->alias("o");
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
                "o.booking_passengers"];

            $list = $query->select($selects)->page($pagination, 20)->asArray()->all();
            foreach($list as &$row){
                $statusInfo = OrderHelper::getOrderRealStatus(
                    $row['order_status'], $row['pay_status'], $row['created_at'],
                    $row['booking_start_date'], $row['booking_days']
                );
                $row['real_status'] = $statusInfo['status'];
                $row['status_text'] = $statusInfo['text'];
                $row['passengers']  = !empty($row['booking_passengers']) ? json_decode($row['booking_passengers']) : [];
                $row['end_date']    = date("Y-m-d", strtotime($row['booking_start_date']) + $row['booking_days'] * 3600 * 24);
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

    /**
     * 查询状态
     * @param ActiveQuery $query
     * @return void
     */
    private function statusFilter(ActiveQuery $query){
        if(!empty($this->status)){
            $method = "statusFor" . ucfirst($this->status);
            if(method_exists($this, $method)){
                $this->$method($query);
            }
        }
    }

    /**
     * 待确认状态
     * @param ActiveQuery $query
     * @return void
     */
    private function statusForUnconfirmed(ActiveQuery $query){
        $query->andWhere([
            "AND",
            ["o.order_status" => "unconfirmed"],
            ["o.pay_status" => "paid"]
        ]);
    }

    /**
     * 已确认状态
     * @param ActiveQuery $query
     * @return void
     */
    private function statusForConfirmed(ActiveQuery $query){
        $query->andWhere([
            "AND",
            ["o.pay_status" => "paid"],
            ["o.order_status" => "success"],
            "(unix_timestamp(o.booking_start_date) + o.booking_days * 3600 * 24) > '".time()."'"
        ]);
    }

    /**
     * 预订失败状态
     * @param ActiveQuery $query
     * @return void
     */
    private function statusForFail(ActiveQuery $query){
        $query->andWhere([
            "AND",
            ["o.pay_status" => "paid"],
            ["o.order_status" => "fail"]
        ]);
    }

    /**
     * 待支付状态
     * @param ActiveQuery $query
     * @return void
     */
    private function statusForUnpaid(ActiveQuery $query){
        $query->andWhere([
            "AND",
            ["o.order_status" => "unpaid"],
            ["o.pay_status" => "unpaid"],
            "o.created_at > '".(time() - 60 * 15)."'"
        ]);
    }

    /**
     * 已结束状态
     * @param ActiveQuery $query
     * @return void
     */
    private function statusForFinished(ActiveQuery $query){
        $query->andWhere(["o.pay_status" => "paid"]);
        $where = "o.order_status = 'finished' OR ";
        $where .= "(o.order_status IN('unconfirmed', 'success') AND (unix_timestamp(o.booking_start_date) + o.booking_days * 3600 * 24) < '".time()."')";
        $query->andWhere($where);
    }

    /**
     * 已取消状态
     * @param ActiveQuery $query
     * @return void
     */
    private function statusForCancel(ActiveQuery $query){
        $query->andWhere(["o.pay_status" => "unpaid"]);
        $query->andWhere([
            "OR",
            ["o.order_status" => "cancel"],
            "o.created_at < '".(time() - 60 * 15)."'"
        ]);
    }
}