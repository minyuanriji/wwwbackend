<?php

namespace app\plugins\hotel\forms\api\user_center;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\hotel\models\HotelOrder;

class UserCenterSaveOrderResidentForm extends BaseModel
{

    public $hotel_order_id;

    public function rules()
    {
        return [
            [['hotel_order_id'], 'required'],
            [['booking_passengers'], 'safe']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {

            //获取订单详情
            $orderDetail = HotelOrder::findOne($this->hotel_order_id);
            if (!$orderDetail) {
                throw new \Exception("订单不存在");
            }

            if ($orderDetail->order_status != 'unpaid') {
                throw new \Exception("订单状态异常，不能修改");
            }

            $orderDetail->booking_passengers = json_encode($this->booking_passengers);
            if (!$orderDetail->save()) {
                throw new \Exception($this->responseErrorMsg($orderDetail));

            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());

        }
    }

}