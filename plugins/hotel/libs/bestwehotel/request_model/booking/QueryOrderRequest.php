<?php
namespace app\plugins\hotel\libs\bestwehotel\request_model\booking;


use app\plugins\hotel\libs\bestwehotel\request_model\BaseRequest;

/**
 * Class QueryOrderRequest
 * @package app\plugins\hotel\libs\bestwehotel\request_model\booking
 * @property string $externalId 第三方订单号
 */
class QueryOrderRequest extends BaseRequest{

    public $externalId;

    public function getUri(){
        return "/booking/queryOrder";
    }
}