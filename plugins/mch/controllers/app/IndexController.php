<?php
namespace app\plugins\mch\controllers\app;


use app\models\Apps;
use yii\web\Controller;

class IndexController extends Controller {

    public function actionIndex(){

        $app = Apps::find()->where([
            "is_delete" => 0,
            "type"      => "merchant",
            "platform"  => "android"
        ])->orderBy("version_code DESC")->one();

        $view = '@app/plugins/' . $this->module->id . '/views/apps/download';

        !$app && die("暂未发布下载版本");

        return $this->renderPartial($view, [
            "app" => $app
        ]);
    }

}