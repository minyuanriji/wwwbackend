<?php
namespace app\plugins\hotel\controllers\api;

use app\plugins\ApiController;
use app\plugins\hotel\forms\api\HotelDetailForm;
use app\plugins\hotel\forms\api\HotelSimpleListForm;
use app\plugins\hotel\libs\bestwehotel\client\hotel\GetHotelRoomStatusClient;
use app\plugins\hotel\libs\bestwehotel\Request;
use app\plugins\hotel\libs\bestwehotel\request_model\hotel\GetHotelRoomStatusRequest;
use app\plugins\hotel\libs\HotelResponse;

class HotelController extends ApiController{

    /**
     * 酒店列表
     * @return \yii\web\Response
     */
    public function actionSimpleList(){
        $form = new HotelSimpleListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());

    }

    /**
     * 酒店信息
     * @return \yii\web\Response
     */
    public function actionDetail(){
        $form = new HotelDetailForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getDetail());
    }
}