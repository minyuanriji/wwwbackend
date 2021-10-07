<?php

namespace app\plugins\shopping_voucher\controllers\mall;

use app\plugins\Controller;
use app\plugins\shopping_voucher\forms\mall\FromHotelListForm;
use app\plugins\shopping_voucher\forms\mall\FromStoreDeleteForm;
use app\plugins\shopping_voucher\forms\mall\FromStoreEditForm;
use app\plugins\shopping_voucher\forms\mall\FromStoreBatchSaveForm;
use app\plugins\shopping_voucher\forms\mall\FromRatioEditForm;
use app\plugins\shopping_voucher\forms\mall\FromStoreSearchStoreForm;

class FromHotelController extends Controller{


    /**
     * 酒店列表
     * @return bool|string|\yii\web\Response
     */
    public function actionList(){

        if (\Yii::$app->request->isAjax) {
            $form = new FromHotelListForm();
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
     * 编辑
     * @return bool|string|\yii\web\Response
     */
    public function actionEditRatio(){
        $form = new FromRatioEditForm();
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

    /**
     * 搜索店铺
     * @return bool|string|\yii\web\Response
     */
    public function actionSearchStore(){
        $form = new FromStoreSearchStoreForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    /**
     * 批量保存
     * @return bool|string|\yii\web\Response
     */
    public function actionBatchSave(){
        $form = new FromStoreBatchSaveForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }
}