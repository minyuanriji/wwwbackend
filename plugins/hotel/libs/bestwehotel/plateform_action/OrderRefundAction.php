<?php
namespace app\plugins\hotel\libs\bestwehotel\plateform_action;


use app\plugins\hotel\libs\bestwehotel\client\booking\CancelOrderClient;
use app\plugins\hotel\libs\bestwehotel\Request;
use app\plugins\hotel\libs\bestwehotel\request_model\booking\CancelOrderRequest;
use app\plugins\hotel\libs\HotelException;
use app\plugins\hotel\libs\HotelResponse;
use app\plugins\hotel\libs\plateform\OrderRefundResult;
use yii\base\BaseObject;

class OrderRefundAction extends BaseObject {

    public $hotelOrder;
    public $plateform_class;

    public function run(){

        $orderRefundResult = new OrderRefundResult();

        try {

            $plateform = $this->hotelOrder->getPlateform();
            if(!$plateform){
                throw new HotelException("订单[ID:".$this->hotelOrder->id."]无法获取到平台信息");
            }

            $requestModel = new CancelOrderRequest([
                "jsonOrderID" => $plateform->plateform_code
            ]);
            $response = Request::execute(new CancelOrderClient($requestModel));
            if(!$response instanceof HotelResponse){
                throw new HotelException("结果对象返回类型[HotelResponse]错误");
            }

            if($response->code != HotelResponse::CODE_SUCC){
                throw new HotelException($response->error);
            }
            $orderRefundResult->code = OrderRefundResult::CODE_SUCC;
        }catch (HotelException $e){
            $orderRefundResult->code = OrderRefundResult::CODE_FAIL;
            $orderRefundResult->message = $e->getMessage();
        }

        return $orderRefundResult;
    }
}