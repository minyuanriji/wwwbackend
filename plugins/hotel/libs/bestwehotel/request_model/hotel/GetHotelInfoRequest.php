<?php
namespace app\plugins\hotel\libs\bestwehotel\request_model\hotel;

use app\plugins\hotel\libs\bestwehotel\request_model\BaseRequest;

/***
 * Class HotelGetHotelInfoModel
 * @package app\plugins\hotel\libs\bestwehotel\request_model
 * @property string $innId 酒店ID
 */
class GetHotelInfoRequest extends BaseRequest {
    public $innId;
}