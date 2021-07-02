<?php
namespace app\plugins\hotel\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\hotel\helpers\OrderHelper;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\hotel\models\HotelRoom;
use app\plugins\hotel\models\Hotels;

class HotelOrderListForm extends BaseModel{


    public function getList(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = HotelOrder::find()->alias("o");
            $query->innerJoin(["ho" => Hotels::tableName()], "ho.id=o.hotel_id");
            $query->innerJoin(["r" => HotelRoom::tableName()], "r.product_code=o.product_code");
            $query->innerJoin(["u" => User::tableName()], "u.id=o.user_id");

            $query->orderBy("o.id DESC");

            $selects = [
                "u.id as user_id", "u.nickname", "ho.name as hotel_name",
                "o.order_status", "o.pay_status", "o.booking_num",
                "o.booking_start_date", "o.order_no", "o.order_price", "o.booking_days",
                "o.pay_at", "o.booking_arrive_date", "o.created_at", "o.updated_at"];

            $list = $query->select($selects)->page($pagination, 20)->asArray()->all();
            foreach($list as &$row){
                $realStatus = OrderHelper::getOrderRealStatus(
                    $row['order_status'], $row['pay_status'], $row['created_at'],
                    $row['booking_start_date'], $row['booking_days']
                );
                print_r($realStatus);
                exit;
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