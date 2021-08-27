<?php

namespace app\plugins\taolijin\controllers\mall;

use app\plugins\Controller;
use app\plugins\taolijin\forms\mall\TaoLiJinAliSearchForm;

class AliController extends Controller{

    /**
     * 阿里联盟商品搜索
     * @return string|\yii\web\Response
     */
    public function actionSearch(){
        $form = new TaoLiJinAliSearchForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

}