<?php
namespace app\plugins\hotel\libs\bestwehotel\client\hotel;


use app\plugins\hotel\libs\bestwehotel\client\BaseClient;
use app\plugins\hotel\libs\bestwehotel\client\IClient;
use app\plugins\hotel\libs\bestwehotel\response_model\HotelGetHotelRoomType\HotelGetHotelRoomTypeDataModel;
use app\plugins\hotel\libs\bestwehotel\response_model\HotelGetHotelRoomType\HotelGetHotelRoomTypeResponseModel;
use app\plugins\hotel\libs\HotelException;

class GetHotelRoomTypeClient extends BaseClient implements IClient {

    public function parseResponseModel($parseArray){
        if(!isset($parseArray['result']) || !isset($parseArray['result']['roomTypeData'])){
            throw new HotelException("[HotelGetHotelRoomTypeClient::parseResponseModel]解析数据错误");
        }

        $responseModel = new HotelGetHotelRoomTypeResponseModel();

        $datas = $parseArray['result']['roomTypeData'];
        foreach($datas as $data){
            $responseModel->datas[] = HotelGetHotelRoomTypeDataModel::create($data);
        }

        return $responseModel;
    }

    public function getUri(){
        return "/hotel/getHotelRoomType";
    }
}