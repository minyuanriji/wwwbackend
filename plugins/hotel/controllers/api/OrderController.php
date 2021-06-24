<?php
namespace app\plugins\hotel\controllers\api;


use app\plugins\ApiController;
use app\plugins\hotel\forms\api\order\HotelOrderPreviewForm;

class OrderController extends ApiController{

    /**
     * 下单预览
     * @return \yii\web\Response
     */
    public function actionPreview(){
        $form = new HotelOrderPreviewForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->preview());
    }

}