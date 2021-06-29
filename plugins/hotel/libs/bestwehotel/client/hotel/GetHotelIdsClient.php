<?php
namespace app\plugins\hotel\libs\bestwehotel\client\hotel;


use app\plugins\hotel\libs\bestwehotel\client\BaseClient;
use app\plugins\hotel\libs\bestwehotel\client\IClient;
use app\plugins\hotel\libs\bestwehotel\response_model\HotelGetHotelIds\HotelGetHotelIdsResponseModel;
use app\plugins\hotel\libs\HotelException;

class GetHotelIdsClient extends BaseClient implements IClient {

    public function parseResponseModel($parseArray){
        if(!isset($parseArray['result'])){
            throw new HotelException("[HotelGetHotelIdsClient::parseResponseModel]解析数据错误");
        }

        $responseModel = HotelGetHotelIdsResponseModel::create($parseArray['result']);
        return $responseModel;
    }
}