<?php
namespace app\plugins\hotel\libs\bestwehotel\request_model\hotel;


use app\plugins\hotel\libs\bestwehotel\request_model\BaseRequest;

/***
 * Class GetHotelImageRequest
 * @package app\plugins\hotel\libs\bestwehotel\request_model\hotel
 * @property string $innId 酒店ID
 */
class GetHotelImageRequest extends BaseRequest {
    public $innId;
}