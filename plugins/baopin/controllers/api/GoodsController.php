<?php
namespace app\plugins\baopin\controllers\api;

use app\helpers\APICacheHelper;
use app\plugins\ApiController;
use app\plugins\baopin\forms\api\SearchForm;

class GoodsController extends ApiController{

    /**
     * 搜索爆品库
     * @return \yii\web\Response
     */
    public function actionSearch(){
        $form = new SearchForm();
        $form->attributes = $this->requestData;
        $form->is_login   = !\Yii::$app->user->isGuest;
        $form->login_uid  = $form->is_login ? \Yii::$app->user->id : 0;

        return $this->asJson(APICacheHelper::get($form));
    }

}