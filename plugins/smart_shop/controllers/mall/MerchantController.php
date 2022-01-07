<?php

namespace app\plugins\smart_shop\controllers\mall;

use app\plugins\Controller;
use app\plugins\smart_shop\forms\mall\MerchantDetailForm;
use app\plugins\smart_shop\forms\mall\MerchantEditForm;
use app\plugins\smart_shop\forms\mall\MerchantGetSmartshopForm;
use app\plugins\smart_shop\forms\mall\MerchantListForm;

class MerchantController extends Controller{

    /**
     * @Note:分账商户
     * @return string|\yii\web\Response
     */
    public function actionIndex() {
        if (\Yii::$app->request->isAjax) {
            $form = new MerchantListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    /**
     * 编辑分账商户
     * @return string|\yii\web\Response
     */
    public function actionEdit(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new MerchantEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new MerchantDetailForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }

    /**
     * 获取智慧门店
     * @return \yii\web\Response
     */
    public function actionGetSmartshop(){
        $form = new MerchantGetSmartshopForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }
}