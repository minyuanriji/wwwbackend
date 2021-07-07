<?php
namespace app\plugins\hotel\libs\bestwehotel\response_model\GetHotelRoomStatus;


use app\plugins\hotel\libs\bestwehotel\response_model\BaseReponseModel;

/**
 * Class GetHotelRoomStatusResponseModel
 * @package app\plugins\hotel\libs\bestwehotel\response_model\GetHotelRoomStatus
 * @property array<RoomTypeListItemResponseModel> $roomTypeList 房型编号
 */
class GetHotelRoomStatusResponseModel extends BaseReponseModel{

    public $roomTypeList = [];

    public function setRoomTypeList($items){
        $this->roomTypeList = [];
        foreach($items as $item){
            $this->roomTypeList[] = RoomTypeListItemResponseModel::create($item);
        }
    }
}