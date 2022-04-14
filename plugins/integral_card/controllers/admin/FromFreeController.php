<?php

namespace app\plugins\integral_card\controllers\admin;

use app\plugins\Controller;
use app\plugins\integral_card\forms\mall\FromFreeDeleteForm;
use app\plugins\integral_card\forms\mall\FromFreeEditForm;
use app\plugins\integral_card\forms\mall\FromFreeListForm;

class FromFreeController extends Controller{

    /**
     * 规则列表
     * @return bool|string|\yii\web\Response
     */
    public function actionList(){

        if (\Yii::$app->request->isAjax) {
            $form = new FromFreeListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('list');
        }
    }

    /**
     * 编辑保存
     * @return bool|string|\yii\web\Response
     */
    public function actionEdit(){
        $form = new FromFreeEditForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 删除店铺
     * @return bool|string|\yii\web\Response
     */
    public function actionDelete(){
        $form = new FromFreeDeleteForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }

}