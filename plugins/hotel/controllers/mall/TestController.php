<?php
namespace app\plugins\hotel\controllers\mall;

use app\plugins\hotel\forms\common\GetHotelsForm;
use app\plugins\hotel\libs\bestwehotel\client\HotelGetHotelIdsClient;
use app\plugins\hotel\libs\bestwehotel\client\HotelGetHotelInfoClient;
use app\plugins\hotel\libs\bestwehotel\Request;
use app\plugins\hotel\libs\bestwehotel\request_model\HotelGetHotelIdsModel;
use app\plugins\hotel\libs\bestwehotel\request_model\HotelGetHotelInfoModel;
use app\plugins\hotel\libs\HotelResponse;
use yii\web\Controller;

class TestController extends Controller{

    public function actionIndex(){
        $form = new GetHotelsForm([
            "page" => 1,
        ]);
        print_r($form->getList());
        exit;


        $hotelGetHotelIdsModel = new HotelGetHotelInfoModel([
            "innId"  => 1000005
        ]);
        $client = new HotelGetHotelInfoClient($hotelGetHotelIdsModel);

        $response = Request::execute($client);
        if($response->code != HotelResponse::CODE_SUCC){
            echo $response->error;
        }else{
            print_r($response->responseModel);
        }
    }
}