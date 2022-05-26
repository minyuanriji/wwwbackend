<?php

namespace app\plugins\perform_distribution\controllers\mall;

use app\plugins\Controller;
use app\plugins\perform_distribution\forms\mall\UserDeleteForm;
use app\plugins\perform_distribution\forms\mall\UserEditForm;
use app\plugins\perform_distribution\forms\mall\UserListForm;

class MemberController extends Controller{

    /**
     * 获取员工数据
     * @return string|yii\web\Response
     */
    public function actionIndex(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {

            } else {
                $form = new UserListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        } else {
            return $this->render('index');
        }
    }

    /**
     * 编辑人员
     * @return void|\yii\web\Response
     */
    public function actionEdit(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new UserEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }
        }
    }

    /**
     * 删除
     * @return \yii\web\Response
     */
    public function actionDelete(){

        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new UserDeleteForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }
        }
    }
}