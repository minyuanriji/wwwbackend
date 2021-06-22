<?php
namespace app\plugins\hotel\libs\bestwehotel\response_model\GetHotelRoomStatus;

use app\plugins\hotel\libs\bestwehotel\response_model\BaseReponseModel;

/**
 * Class GetHotelRoomStatusResponseModel
 * @package app\plugins\hotel\libs\bestwehotel\response_model\GetHotelRoomStatus
 * @property string $roomTypeCode 房型编号
 * @property string $roomTypeName 房型名称
 * @property array<ProductDataResponseModel> $productList 商品列表
 */
class RoomTypeListItemResponseModel extends BaseReponseModel
{
    public $roomTypeCode;
    public $roomTypeName;
    public $productList = [];

    public function setProductList($items = []){
        $this->productList = [];
        foreach($items as $item){
            $this->productList[] = ProductDataResponseModel::create($item);
        }
    }
}