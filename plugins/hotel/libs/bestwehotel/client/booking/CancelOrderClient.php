<?php
namespace app\plugins\hotel\libs\bestwehotel\client\booking;


use app\plugins\hotel\libs\bestwehotel\client\BaseClient;
use app\plugins\hotel\libs\bestwehotel\client\IClient;
use app\plugins\hotel\libs\bestwehotel\response_model\CancelOrder\CancelOrderResponseModel;

class CancelOrderClient extends BaseClient implements IClient {

    public function parseResponseModel($parseArray){
        $responseModel = CancelOrderResponseModel::create([]);
        return $responseModel;
    }
}