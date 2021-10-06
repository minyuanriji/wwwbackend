<?php

namespace app\plugins\shopping_voucher\controllers\mall;

use app\plugins\Controller;

class StatController extends Controller{

    /**
     * 数据概况
     * @return bool|string|\yii\web\Response
     */
    public function actionIndex(){
        return $this->render('index');
    }

}