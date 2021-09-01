<?php

namespace app\plugins\shopping_voucher\controllers\mall;

use app\plugins\shopping_voucher\forms\mall\DeleteForm;
use app\plugins\shopping_voucher\forms\mall\MchListForm;
use app\plugins\shopping_voucher\forms\mall\StoreEditForm;
use app\plugins\shopping_voucher\forms\mall\StoreListForm;
use app\plugins\Controller;

class StoreController extends Controller
{
    /**
     * 商户列表
     */
    public function actionList(){
        if (\Yii::$app->request->isAjax) {
            $form = new StoreListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    /**
     * @Note:编辑商户
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new StoreEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                $res = $form->save();
                return $this->asJson($res);
            } else {
                $form = new StoreEditForm();
                $form->attributes = \Yii::$app->request->get();
                $detail = $form->getDetail();
                return $this->asJson($detail);
            }
        } else {
            return $this->render('edit');
        }
    }

    /**
     * 获取商户列表
     */
    public function actionMchList(){
        if (\Yii::$app->request->isAjax) {
            $form = new MchListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        }
    }

    /**
     * 删除商户记录
     * @return
     */
    public function actionDeleteMch()
    {
        $form = new DeleteForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }
}