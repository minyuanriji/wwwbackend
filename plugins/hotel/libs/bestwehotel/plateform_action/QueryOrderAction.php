<?php


namespace app\plugins\hotel\libs\bestwehotel\plateform_action;


use app\helpers\ArrayHelper;
use app\plugins\hotel\libs\bestwehotel\client\booking\QueryOrderClient;
use app\plugins\hotel\libs\bestwehotel\Request;
use app\plugins\hotel\libs\bestwehotel\request_model\booking\QueryOrderRequest;
use app\plugins\hotel\libs\HotelException;
use app\plugins\hotel\libs\HotelResponse;
use app\plugins\hotel\libs\plateform\QueryOrderResult;
use yii\base\BaseObject;

class QueryOrderAction extends BaseObject {

    public $hotelOrder;
    public $plateform_class;

    public function run(){
        $queryOrderResult = new QueryOrderResult();
        try {
            $requestModel = new QueryOrderRequest([
                "externalId" => $this->hotelOrder->order_no
            ]);
            $response = Request::execute(new QueryOrderClient($requestModel));
            if(!$response instanceof HotelResponse){
                throw new HotelException("结果对象返回类型[HotelResponse]错误");
            }

            if($response->code != HotelResponse::CODE_SUCC){
                throw new HotelException($response->error);
            }

            $responseModel = $response->responseModel;

            $queryOrderResult->code = QueryOrderResult::CODE_SUCC;
            $queryOrderResult->plateform_order_no = $responseModel->orderCode;
            $queryOrderResult->order_state = $responseModel->orderState;
            $queryOrderResult->pay_state = $responseModel->payState;
            $queryOrderResult->pay_type = $responseModel->payType;
            $queryOrderResult->origin_data = ArrayHelper::toArray($responseModel);
        }catch (HotelException $e){
            $queryOrderResult->code = QueryOrderResult::CODE_FAIL;
            $queryOrderResult->message = $e->getMessage();
        }
        return $queryOrderResult;
    }
}