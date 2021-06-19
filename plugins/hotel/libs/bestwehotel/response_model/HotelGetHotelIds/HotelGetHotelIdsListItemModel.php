<?php
namespace app\plugins\hotel\libs\bestwehotel\response_model\HotelGetHotelIds;


use yii\base\Model;

/***
 * Class HotelGetHotelIdsListItemModel
 * @package app\plugins\hotel\libs\bestwehotel\response_model\HotelGetHotelIds
 * @property int $innId 酒店唯一编号
 * @property int $brandCode
 */
class HotelGetHotelIdsListItemModel extends Model
{
    public $innId;
    public $brandCode;
}