<?php
namespace app\plugins\hotel\libs\bestwehotel\request_model\hotel;


use app\plugins\hotel\libs\bestwehotel\request_model\BaseRequest;

/***
 * Class GetHotelRoomStatusRequest
 * @package app\plugins\hotel\libs\bestwehotel\request_model\hotel
 * @property string $innId 酒店唯一编号
 * @property string $endOfDay 营业日期(yyyy-MM-dd)
 * @property string $roomTypeCode 可选-房型编号(1001 大床房 1002 经济房…)
 * @property int $days 可选-查询天数(默认1天)
 * @property int $priceType 可选-1/null 基础协议价 2 追价(基础协议价+普卡活动价)
 */
class GetHotelRoomStatusRequest extends BaseRequest{

    public $innId;
    public $roomTypeCode;
    public $endOfDay;
    public $days;
    public $priceType;

    public function getUri(){
        return "/hotel/getHotelRoomStatus";
    }
}