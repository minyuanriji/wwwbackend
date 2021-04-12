<?php
namespace app\mch\controllers;


use app\mch\forms\baopin\BaopinDeleteForm;
use app\mch\forms\baopin\BaopinDeleteMutiForm;
use app\mch\forms\baopin\BaopinEditSortForm;
use app\mch\forms\baopin\BaopinImportForm;
use app\mch\forms\baopin\GoodsListForm;

class BaopinController extends MchController {

    /**
     * 爆品记录
     * @return bool|string|\yii\web\Response
     */
    public function actionIndex(){
        if (\Yii::$app->request->isAjax) {
            $form = new GoodsListForm();
            $form->attributes = \Yii::$app->request->get();
            $form->mch_id     = \Yii::$app->mchId;
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    /**
     * 导入爆品
     * @return bool|string|\yii\web\Response
     */
    public function actionImport(){
        $form = new BaopinImportForm();
        $form->attributes = \Yii::$app->request->post();
        $form->mch_id     = \Yii::$app->mchId;
        return $this->asJson($form->import());
    }

    /**
     * 删除爆品
     * @return bool|string|\yii\web\Response
     */
    public function actionDelete(){
        $form = new BaopinDeleteForm();
        $form->attributes = \Yii::$app->request->post();
        $form->mch_id     = \Yii::$app->mchId;
        return $this->asJson($form->delete());
    }

    /**
     * 批量删除爆品
     * @return bool|string|\yii\web\Response
     */
    public function actionDeleteMuti(){
        $form = new BaopinDeleteMutiForm();
        $form->attributes = \Yii::$app->request->post();
        $form->mch_id     = \Yii::$app->mchId;
        return $this->asJson($form->deleteMuti());
    }

    /**
     * 编辑排序
     * @return bool|string|\yii\web\Response
     */
    public function actionEditSort(){
        $form = new BaopinEditSortForm();
        $form->attributes = \Yii::$app->request->post();
        $form->mch_id     = \Yii::$app->mchId;
        return $this->asJson($form->save());
    }
}