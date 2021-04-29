<?php
namespace app\mch\controllers;


use app\forms\mall\option\RechargeSettingForm;
use app\forms\mall\setting\MallForm;
use app\forms\mall\setting\SettingForm;

class SettingController extends MchController{

    public function actionIndex(){

        return $this->render("index");
    }

}