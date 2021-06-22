<?php
namespace app\plugins\hotel\libs\bestwehotel\response_model\HotelGetHotelIds;

use app\plugins\hotel\libs\bestwehotel\response_model\BaseReponseModel;

/***
 * Class HotelGetHotelIdsResponseModel
 * @package app\plugins\hotel\libs\bestwehotel\response_model\HotelGetHotelIds
 * @property int $pageNum   页码，从1开始
 * @property int $pageSize  页面大小
 * @property int $total     总数
 * @property int $pages     总页数
 * @property array<HotelGetHotelIdsListItemModel> $list 酒店id列表
 */
class HotelGetHotelIdsResponseModel extends BaseReponseModel
{
    public $pageNum;
    public $pageSize;
    public $total;
    public $pages;
    public $list = [];

    public function setList($items){
        $this->list = [];
        foreach($items as $item){
            $this->list[] = HotelGetHotelIdsListItemModel::create($item);
        }
    }
}