<?php
namespace app\plugins\hotel\libs\bestwehotel\client\booking;


use app\plugins\hotel\libs\bestwehotel\client\BaseClient;
use app\plugins\hotel\libs\bestwehotel\client\IClient;
use app\plugins\hotel\libs\bestwehotel\response_model\QueryOrder\QueryOrderResponseModel;

class QueryOrderClient extends BaseClient implements IClient {

    public function parseResponseModel($parseArray){
        if(!isset($parseArray['result'])){
            throw new HotelException("[QueryOrderClient::parseResponseModel]解析数据错误");
        }

        $responseModel = QueryOrderResponseModel::create($parseArray['result']);

        return $responseModel;
    }
}