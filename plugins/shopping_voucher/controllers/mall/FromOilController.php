<?php

namespace app\plugins\shopping_voucher\controllers\mall;

use app\plugins\Controller;
use app\plugins\shopping_voucher\forms\mall\FromOilBatchSaveForm;
use app\plugins\shopping_voucher\forms\mall\FromOilCommonSaveForm;
use app\plugins\shopping_voucher\forms\mall\FromOilDeleteForm;
use app\plugins\shopping_voucher\forms\mall\FromOilListForm;
use app\plugins\shopping_voucher\forms\mall\FromOilSaveForm;
use app\plugins\shopping_voucher\forms\mall\FromOilSearchOilPlateformForm;
use app\plugins\shopping_voucher\forms\mall\FromOilSearchOilProductForm;

class FromOilController extends Controller{

    /**
     * 大礼包列表
     * @return bool|string|\yii\web\Response
     */
    public function actionList(){
        if (\Yii::$app->request->isAjax) {
            $form = new FromOilListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('list');
        }
    }

    /**
     * 保存通用配置
     * @return bool|string|\yii\web\Response
     */
    public function actionSaveCommon(){
        $form = new FromOilCommonSaveForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 搜索加油平台
     * @return bool|string|\yii\web\Response
     */
    public function actionSearchOilPlateform(){
        $form = new FromOilSearchOilPlateformForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    /**
     * 搜索加油产品
     * @return bool|string|\yii\web\Response
     */
    public function actionSearchOilProduct(){
        $form = new FromOilSearchOilProductForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    /**
     * 批量保存
     * @return bool|string|\yii\web\Response
     */
    public function actionBatchSave(){
        $form = new FromOilBatchSaveForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 删除赠送规则
     * @return bool|string|\yii\web\Response
     */
    public function actionDelete(){
        $form = new FromOilDeleteForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }

    /**
     * 指定规则编辑
     * @return bool|string|\yii\web\Response
     */
    public function actionSave(){
        $form = new FromOilSaveForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }
}