<?php
namespace app\plugins\hotel\libs\bestwehotel\response_model\HotelGetHotelIds;


use app\plugins\hotel\libs\bestwehotel\response_model\BaseReponseModel;

/***
 * Class HotelGetHotelIdsListItemModel
 * @package app\plugins\hotel\libs\bestwehotel\response_model\HotelGetHotelIds
 * @property int $innId 酒店唯一编号
 * @property int $brandCode
 */
class HotelGetHotelIdsListItemModel extends BaseReponseModel
{
    public $innId;
    public $brandCode;
}