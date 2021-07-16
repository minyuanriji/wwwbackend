<?php
namespace app\plugins\hotel\libs\bestwehotel\request_model\hotel;

use app\plugins\hotel\libs\bestwehotel\request_model\BaseRequest;

/***
 * Class HotelGetHotelIdsModel
 * @package app\plugins\hotel\libs\bestwehotel\request_model
 * @property int $pageNum  当前页码，默认为1
 * @property int $pageSize 页面记录数量，默认10，最大1000
 */
class GetHotelIdsRequest extends BaseRequest {

    public $pageNum;
    public $pageSize;
    public $Status = 1;
    public $flagbook = 1;

    public function getUri(){
        return "/hotel/getHotelIds";
    }
}