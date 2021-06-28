<?php
namespace app\plugins\hotel\forms\api\user_center;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\DistrictData;
use app\plugins\hotel\helpers\OrderHelper;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\hotel\models\HotelRoom;
use app\plugins\hotel\models\Hotels;

class UserCenterOrderListForm extends BaseModel {

    public $page;
    public $status;

    public function rules(){
        return [
            [['page'], 'integer'],
            [['status'], 'safe']
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = HotelOrder::find()->alias("o");
            $query->innerJoin(["h" => Hotels::tableName()], "h.id=o.hotel_id");
            $query->innerJoin(["r" => HotelRoom::tableName()], "r.hotel_id=h.id AND r.product_code=o.product_code");

            $selects = [
                "h.thumb_url", "h.name as hotel_name", "h.tx_lat", "h.tx_lng", "h.contact_phone",
                "h.contact_mobile", "h.address", "h.province_id", "h.city_id",
                "h.district_id", "h.type as hotel_type",
                "r.bed_type", "r.name as room_name", "r.bed_width", "r.floor", "r.window",
                "r.policy_ban_smoking", "r.policy_add_bed",
                "o.id as order_id", "o.hotel_id", "o.order_status", "o.order_no", "o.order_price", "o.booking_num",
                "o.booking_start_date", "o.booking_days", "o.booking_arrive_date",
                "o.created_at", "o.pay_status", "o.pay_at", "o.pay_price", "o.integral_deduction_price",
                "o.integral_fee_rate"
            ];

            $query->select($selects);

            $rows = $query->page($pagination, 10, max(1, (int)$this->page))
                          ->asArray()->all();

            $districtArr = DistrictData::getArr();
            foreach($rows as &$row){
                $statusInfo = OrderHelper::getOrderRealStatus($row['order_status'], $row['pay_status'], $row['created_at'], $row['booking_start_date'], $row['booking_days']);
                $row['status_text'] = $statusInfo['text'];
                $row['real_status'] = $statusInfo['status'];
                $row['province_name'] = $row['city_name'] = "";
                if(isset($districtArr[$row['province_id']])){
                    $row['province_name'] = $districtArr[$row['province_id']]['name'];
                }
                if(isset($districtArr[$row['city_id']])){
                    $row['city_name'] = $districtArr[$row['city_id']]['name'];
                }
                unset($row['order_status']);
                unset($row['pay_status']);
                $row['created_at'] = date("Y-m-d H:i", $row['created_at']);
                $row['end_date'] = date("Y-m-d", strtotime($row['booking_start_date']) +$row['booking_days'] * 3600 * 24);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $rows ? $rows : [],
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