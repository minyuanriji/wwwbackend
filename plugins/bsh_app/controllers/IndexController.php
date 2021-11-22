<?php
namespace app\plugins\bsh_app\controllers;


use app\models\Apps;
use yii\web\Controller;

class IndexController extends Controller {

    public function actionIndex(){

        $app = Apps::find()->where([
            "is_delete" => 0,
            "type"      => "bsh",
            "platform"  => "android"
        ])->orderBy("version_code DESC")->one();

        $view = '@app/plugins/' . $this->module->id . '/views/h5/index';

        !$app && die("暂未发布下载版本");

        return $this->renderPartial($view, [
            "app" => $app
        ]);
    }

}