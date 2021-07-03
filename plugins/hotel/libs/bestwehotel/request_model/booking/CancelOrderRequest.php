<?php
namespace app\plugins\hotel\libs\bestwehotel\request_model\booking;


use app\plugins\hotel\libs\bestwehotel\request_model\BaseRequest;

/**
 * Class CancelOrderRequest
 * @package app\plugins\hotel\libs\bestwehotel\request_model\booking
 * @property string $jsonOrderID 订单id
 */
class CancelOrderRequest extends BaseRequest{

    public $jsonOrderID;

    public function getUri() {
        return "/booking/cancelOrder";
    }
}