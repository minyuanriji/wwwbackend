<?php
namespace app\plugins\hotel\controllers\api;

use app\plugins\ApiController;
use app\plugins\hotel\forms\api\HotelListForm;

class HotelController extends ApiController{

    /**
     * 酒店列表
     * @return \yii\web\Response
     */
    public function actionList(){
        $form = new HotelListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

}