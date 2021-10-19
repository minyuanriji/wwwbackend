<?php

namespace app\plugins\shopping_voucher\controllers\mall;

use app\plugins\Controller;
use app\plugins\shopping_voucher\forms\mall\FromGoodsBatchSaveForm;
use app\plugins\shopping_voucher\forms\mall\FromGoodsCommonSaveForm;
use app\plugins\shopping_voucher\forms\mall\FromGoodsDeleteForm;
use app\plugins\shopping_voucher\forms\mall\FromGoodsListForm;
use app\plugins\shopping_voucher\forms\mall\FromGoodsSearchGoodsForm;

class FromMallGoodsController extends Controller{

    /**
     * 商品列表
     * @return bool|string|\yii\web\Response
     */
    public function actionList(){

        if (\Yii::$app->request->isAjax) {
            $form = new FromGoodsListForm();
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
        $form = new FromGoodsCommonSaveForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 搜索店铺
     * @return bool|string|\yii\web\Response
     */
    public function actionSearchGoods(){
        $form = new FromGoodsSearchGoodsForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    /**
     * 批量保存
     * @return bool|string|\yii\web\Response
     */
    public function actionBatchSave(){
        $form = new FromGoodsBatchSaveForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 删除商品
     * @return bool|string|\yii\web\Response
     */
    public function actionDelete(){
        $form = new FromGoodsDeleteForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }
}