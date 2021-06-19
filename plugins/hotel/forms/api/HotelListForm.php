<?php
namespace app\plugins\hotel\forms\api;

use app\core\ApiCode;
use app\core\BasePagination;
use app\helpers\ArrayHelper;
use app\models\BaseModel;
use app\plugins\hotel\libs\bestwehotel\client\HotelGetHotelIdsClient;
use app\plugins\hotel\libs\bestwehotel\client\HotelGetHotelInfoClient;
use app\plugins\hotel\libs\bestwehotel\Formatter;
use app\plugins\hotel\libs\bestwehotel\Request;
use app\plugins\hotel\libs\bestwehotel\request_model\HotelGetHotelIdsModel;
use app\plugins\hotel\libs\bestwehotel\request_model\HotelGetHotelInfoModel;
use app\plugins\hotel\libs\HotelResponse;

class HotelListForm extends BaseModel{

    public $page;

    public function rules(){
        return [
            [['page'], 'integer']
        ];
    }

    public function getList(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $pageSize = 12;

        try {
            $hotelGetHotelIdsModel = new HotelGetHotelIdsModel([
                "pageNum"  => $this->page,
                "pageSize" => $pageSize
            ]);
            $client = new HotelGetHotelIdsClient($hotelGetHotelIdsModel);

            $response = Request::execute($client);
            if($response->code != HotelResponse::CODE_SUCC){
                throw new \Exception($response->error);
            }

            $pagination = new BasePagination([
                'totalCount' => $response->responseModel->total,
                'pageSize'   => $pageSize,
                'page'       => $response->responseModel->pageNum
            ]);

            $list = [];
            $items = $response->responseModel->list;
            foreach($items as $item){

                $hotelGetHotelIdsModel = new HotelGetHotelInfoModel([
                    "innId"  => $item->innId
                ]);
                $client = new HotelGetHotelInfoClient($hotelGetHotelIdsModel);

                $response = Request::execute($client);
                if($response->code != HotelResponse::CODE_SUCC){
                    continue;
                }

                $hotelInfo = Formatter::hotelInfo($response);

                $list[] = $hotelInfo;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
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