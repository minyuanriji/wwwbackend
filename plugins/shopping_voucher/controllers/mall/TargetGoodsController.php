<?php

namespace app\plugins\shopping_voucher\controllers\mall;

use app\plugins\Controller;
use app\plugins\shopping_voucher\forms\mall\ShoppingVoucherGoodsDeleteForm;
use app\plugins\shopping_voucher\forms\mall\ShoppingVoucherGoodsEditForm;
use app\plugins\shopping_voucher\forms\mall\ShoppingVoucherGoodsListForm;

class TargetGoodsController extends Controller{

    /**
     * 购物券商品管理
     * @return bool|string|\yii\web\Response
     */
    public function actionList(){
        if (\Yii::$app->request->isAjax) {
            $form = new ShoppingVoucherGoodsListForm();
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
        $form = new ShoppingVoucherGoodsEditForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 删除购物券商品
     * @return bool|string|\yii\web\Response
     */
    public function actionDelete(){
        $form = new ShoppingVoucherGoodsDeleteForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }
}