<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-06-28
 * Time: 17:04
 */

namespace app\controllers\api;


use app\forms\api\express\ExpressForm;

class ExpressController extends ApiController
{
    public function actionQuery()
    {
        $form = new ExpressForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    public function actionNewQuery()
    {
        $form = new ExpressForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

}