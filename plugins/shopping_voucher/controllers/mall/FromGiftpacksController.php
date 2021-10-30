<?php

namespace app\plugins\shopping_voucher\controllers\mall;

use app\plugins\Controller;
use app\plugins\shopping_voucher\forms\mall\FromGiftpacksBatchSaveForm;
use app\plugins\shopping_voucher\forms\mall\FromGiftpacksCommonSaveForm;
use app\plugins\shopping_voucher\forms\mall\FromGiftpacksDeleteForm;
use app\plugins\shopping_voucher\forms\mall\FromGiftpacksListForm;
use app\plugins\shopping_voucher\forms\mall\FromGiftpacksSaveForm;
use app\plugins\shopping_voucher\forms\mall\FromGiftpacksSearchGiftpacksForm;

class FromGiftpacksController extends Controller{

    /**
     * 大礼包列表
     * @return bool|string|\yii\web\Response
     */
    public function actionList(){
        if (\Yii::$app->request->isAjax) {
            $form = new FromGiftpacksListForm();
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
        $form = new FromGiftpacksCommonSaveForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 搜索大礼包
     * @return bool|string|\yii\web\Response
     */
    public function actionSearchGiftpacks(){
        $form = new FromGiftpacksSearchGiftpacksForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    /**
     * 批量保存
     * @return bool|string|\yii\web\Response
     */
    public function actionBatchSave(){
        $form = new FromGiftpacksBatchSaveForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 删除商品
     * @return bool|string|\yii\web\Response
     */
    public function actionDelete(){
        $form = new FromGiftpacksDeleteForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }

    /**
     * 指定大礼包编辑
     * @return bool|string|\yii\web\Response
     */
    public function actionSave(){
        $form = new FromGiftpacksSaveForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }
}