<?php
namespace app\plugins\hotel\controllers\mall;


use app\plugins\Controller;
use app\plugins\hotel\forms\mall\HotelOrderListForm;

class OrderController extends Controller{

    public function actionList(){
        if (\Yii::$app->request->isAjax) {
            $form = new HotelOrderListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

}