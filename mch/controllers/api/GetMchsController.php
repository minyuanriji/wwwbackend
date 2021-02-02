<?php
namespace app\mch\controllers\api;

use app\controllers\api\ApiController;
use app\mch\forms\common\CommonMchForm;

class GetMchsController extends ApiController {

    /**
     * 获取商户
     * @return \yii\web\Response
     */
    public function actionIndex(){

        $form = new CommonMchForm();
        $form->attributes = \Yii::$app->request->post();

        $this->asJson($form->getList());
    }
}