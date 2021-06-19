<?php
namespace app\plugins\hotel\libs\bestwehotel\client\hotel;


use app\plugins\hotel\libs\bestwehotel\client\BaseClient;
use app\plugins\hotel\libs\bestwehotel\client\IClient;
use app\plugins\hotel\libs\bestwehotel\response_model\HotelGetHotelIds\HotelGetHotelIdsListItemModel;
use app\plugins\hotel\libs\bestwehotel\response_model\HotelGetHotelIds\HotelGetHotelIdsResponseModel;
use app\plugins\hotel\libs\HotelException;

class GetHotelIdsClient extends BaseClient implements IClient {

    public function getUri(){
        return "/hotel/getHotelIds";
    }

    public function parseResponseModel($parseArray){
        if(!isset($parseArray['result'])){
            throw new HotelException("[HotelGetHotelIdsClient::parseResponseModel]解析数据错误");
        }

        $result = $parseArray['result'];
        $responseModel = new HotelGetHotelIdsResponseModel([
            "pageNum"  => isset($result['pageNum']) ? (int)$result['pageNum'] : 0,
            "pageSize" => isset($result['pageSize']) ? (int)$result['pageSize'] : 0,
            "total"    => isset($result['total']) ? (int)$result['total'] : 0,
            "pages"    => isset($result['pages']) ? (int)$result['pages'] : 0,
        ]);

        $list = isset($result['list']) && is_array($result['list']) ? $result['list'] : [];
        foreach($list as $item){
            $responseModel->list[] = new HotelGetHotelIdsListItemModel($item);
        }

        return $responseModel;
    }
}