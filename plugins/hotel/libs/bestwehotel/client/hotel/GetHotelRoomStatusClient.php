<?php
namespace app\plugins\hotel\libs\bestwehotel\client\hotel;


use app\plugins\hotel\libs\bestwehotel\client\BaseClient;
use app\plugins\hotel\libs\bestwehotel\client\IClient;
use app\plugins\hotel\libs\bestwehotel\response_model\GetHotelRoomStatus\GetHotelRoomStatusResponseModel;
use app\plugins\hotel\libs\HotelException;

class GetHotelRoomStatusClient extends BaseClient implements IClient {

    public function parseResponseModel($parseArray){
        if(!isset($parseArray['result']) || !isset($parseArray['result']['roomTypeList'])){
            throw new HotelException("[GetHotelRoomStatusClient::parseResponseModel]解析数据错误");
        }
        $responseModel = GetHotelRoomStatusResponseModel::create($parseArray['result']);
        return $responseModel;
    }
}