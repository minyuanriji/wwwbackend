<?php
namespace app\plugins\hotel\libs\bestwehotel\client\booking;


use app\plugins\hotel\libs\bestwehotel\client\BaseClient;
use app\plugins\hotel\libs\bestwehotel\client\IClient;

class PostOrderClient extends BaseClient implements IClient {

    public function getDataJSONString(){
        $json = parent::getDataJSONString();
        //echo $json;
        //exit;
        return $json;
    }

    public function parseResponseModel($parseArray){

        print_r($parseArray);
        exit;

    }
}