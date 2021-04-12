<?php
namespace app\plugins\baopin\controllers\api;

use app\plugins\ApiController;
use app\plugins\baopin\forms\api\SearchForm;

class GoodsController extends ApiController{

    public function actionSearch(){
        $form = new SearchForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->search());
    }

}