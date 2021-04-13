<?php
namespace app\plugins\baopin\controllers\api;

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
        return $this->asJson($form->search());
    }

}