<?php
namespace app\plugins\hotel\libs\bestwehotel\request_model\hotel;

use app\plugins\hotel\libs\bestwehotel\request_model\BaseRequest;

/***
 * Class HotelGetHotelRoomTypeModel
 * @package app\plugins\hotel\libs\bestwehotel\request_model
 * @property string $innId 酒店唯一编号
 */
class GetHotelRoomTypeRequest extends BaseRequest {

    public $innId;

    public function getUri(){
        return "/hotel/getHotelRoomType";
    }
}