<?php
namespace app\plugins\hotel\libs\bestwehotel\plateform_action;

use yii\base\BaseObject;

class RefundableAction extends BaseObject {

    public $hotelOrder;
    public $plateform_class;

    public function run(){
        $isRefundable = false;
        try {
            $originData = @json_decode($this->hotelOrder->origin_booking_data, true);
            if((time() * 1000) < $originData['freeCancelTime']){
                $isRefundable = true;
            }
        }catch (\Exception $e){
            $isRefundable = false;
        }
        return $isRefundable;
    }

}