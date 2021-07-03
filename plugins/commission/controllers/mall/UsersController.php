<?php
namespace app\plugins\commission\controllers\mall;

use app\plugins\Controller;
use Yii;

class UsersController extends Controller{

    public function actionList(){

        if (Yii::$app->request->isAjax) {

        }

        return $this->render('list');
    }

}