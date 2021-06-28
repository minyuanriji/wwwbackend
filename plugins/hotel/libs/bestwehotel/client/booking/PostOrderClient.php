<?php
namespace app\plugins\hotel\libs\bestwehotel\client\booking;


use app\plugins\hotel\libs\bestwehotel\client\BaseClient;
use app\plugins\hotel\libs\bestwehotel\client\IClient;
use app\plugins\hotel\libs\bestwehotel\response_model\PostOrder;
use app\plugins\hotel\libs\HotelException;

class PostOrderClient extends BaseClient implements IClient {

    public function parseResponseModel($parseArray){

        if(!isset($parseArray['result'])){
            throw new HotelException("[PostOrderClient::parseResponseModel]解析数据错误");
        }

        $responseModel = PostOrder::create($parseArray['result']);

        return $responseModel;
    }
}