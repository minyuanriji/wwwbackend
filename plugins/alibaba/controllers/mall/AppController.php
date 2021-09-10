<?php

namespace app\plugins\alibaba\controllers\mall;

use app\plugins\alibaba\forms\mall\AlibabaAppEditForm;
use app\plugins\alibaba\forms\mall\AlibabaAppListForm;
use app\plugins\Controller;

class AppController extends Controller{

    /**
     * 应用列表
     * @return string|\yii\web\Response
     */
    public function actionList(){
        if (\Yii::$app->request->isAjax) {
            $form = new AlibabaAppListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('list');
        }
    }

    /**
     * 编辑应用
     * @return string|\yii\web\Response
     */
    public function actionEdit(){
        $form = new AlibabaAppEditForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }
}