<?php
namespace app\mch\controllers\api;

use app\controllers\api\ApiController;
use app\helpers\APICacheHelper;
use app\mch\forms\api\GetMchsForm;

class GetMchsController extends ApiController {

    /**
     * 获取商户
     * @return \yii\web\Response
     */
    public function actionIndex(){

        $form = new GetMchsForm();
        $form->attributes = $this->requestData;
        $form->city_id    = \Yii::$app->request->headers->get("x-city-id");
        $form->longitude  = ApiController::$cityData['longitude'];
        $form->latitude   = ApiController::$cityData['latitude'];
        $form->is_login   = !\Yii::$app->user->isGuest;
        $form->login_uid  = $form->is_login ? \Yii::$app->user->id : 0;

        return $this->asJson(APICacheHelper::get($form));
    }
}