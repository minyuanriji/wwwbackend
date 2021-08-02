<?php
namespace app\plugins\hotel\libs\bestwehotel\client\hotel;

use app\plugins\hotel\libs\bestwehotel\client\BaseClient;
use app\plugins\hotel\libs\bestwehotel\client\IClient;
use app\plugins\hotel\libs\bestwehotel\response_model\GetHotelImage\GetHotelImageDataModel;
use app\plugins\hotel\libs\bestwehotel\response_model\GetHotelImage\GetHotelImageResponseModel;
use app\plugins\hotel\libs\HotelException;

class GetHotelImageClient extends BaseClient implements IClient {

    public function parseResponseModel($parseArray){
        if(!isset($parseArray['result']) || !isset($parseArray['result']['imageDatas'])){
            throw new HotelException("[GetHotelImageClient::parseResponseModel]解析数据错误");
        }
        $datas = $parseArray['result']['imageDatas'];
        $responseModel = new GetHotelImageResponseModel();
        foreach($datas as $data){
            $responseModel->datas[] = GetHotelImageDataModel::create($data);
        }
        return $responseModel;
    }
}