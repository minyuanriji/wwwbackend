<?php

namespace app\plugins\integral_card\controllers\admin;

use app\plugins\Controller;
use app\plugins\integral_card\forms\mall\FromStoreDeleteForm;
use app\plugins\integral_card\forms\mall\FromStoreEditForm;
use app\plugins\integral_card\forms\mall\FromStoreListForm;

class FromStoreController extends Controller{

    /**
     * 店铺列表
     * @return bool|string|\yii\web\Response
     */
    public function actionList(){

        if (\Yii::$app->request->isAjax) {
            $form = new FromStoreListForm();
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
        $form = new FromStoreEditForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 删除店铺
     * @return bool|string|\yii\web\Response
     */
    public function actionDelete(){
        $form = new FromStoreDeleteForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }
}