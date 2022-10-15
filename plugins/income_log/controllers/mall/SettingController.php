<?php
namespace app\plugins\income_log\controllers\mall;

use app\plugins\Controller;

class SettingController extends Controller{


    public function actionIndex(){

        return $this->render('index');
    }
}