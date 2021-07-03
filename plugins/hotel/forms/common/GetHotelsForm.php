<?php
namespace app\plugins\hotel\forms\common;

use app\core\ApiCode;
use app\core\BasePagination;
use app\helpers\ArrayHelper;
use app\models\BaseModel;
use app\plugins\hotel\libs\bestwehotel\client\HotelGetHotelIdsClient;
use app\plugins\hotel\libs\bestwehotel\client\HotelGetHotelInfoClient;
use app\plugins\hotel\libs\bestwehotel\Request;
use app\plugins\hotel\libs\bestwehotel\request_model\HotelGetHotelIdsModel;
use app\plugins\hotel\libs\bestwehotel\request_model\HotelGetHotelInfoModel;
use app\plugins\hotel\libs\HotelResponse;

class GetHotelsForm extends BaseModel {

    public $page;
    public $limit = 20;

    public function rules(){
        return [
            [['page', 'limit'], 'integer']
        ];
    }

    public function getList(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $hotelGetHotelIdsModel = new HotelGetHotelIdsModel([
                "pageNum"  => $this->page,
                "pageSize" => max(min(5, $this->limit), 100)
            ]);
            $client = new HotelGetHotelIdsClient($hotelGetHotelIdsModel);

            $response = Request::execute($client);
            if($response->code != HotelResponse::CODE_SUCC){
                throw new \Exception($response->error);
            }

            $responseModel = $response->responseModel;

            $pagination = new BasePagination([
                'totalCount' => $responseModel->total,
                'pageSize'   => $this->limit,
                'page'       => $responseModel->pageNum
            ]);

            $list = [];
            foreach($responseModel->list as $item){

                $hotelGetHotelIdsModel = new HotelGetHotelInfoModel([
                    "innId"  => $item->innId
                ]);
                $client = new HotelGetHotelInfoClient($hotelGetHotelIdsModel);

                $response = Request::execute($client);
                if($response->code != HotelResponse::CODE_SUCC){
                    continue;
                }

                $data = ArrayHelper::toArray($response->responseModel);
                $mapInfos = $response->responseModel->getMapInfo();
                foreach($mapInfos as $mapInfo){
                    $data['mapInfo'] = ArrayHelper::toArray($mapInfo);
                }

                $list[] = $data;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => [],
                    'pagination' => $pagination
                ]
            ];

        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}