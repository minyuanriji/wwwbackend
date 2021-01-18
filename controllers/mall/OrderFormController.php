<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 店铺管理-店铺设计-下单表单
 * Author: zal
 * Date: 2020-04-14
 * Time: 10:50
 */

namespace app\controllers\mall;

use app\forms\mall\shop\OrderFormEditForm;
use app\forms\mall\shop\OrderFormForm;
use app\forms\mall\shop\OrderFormUpdate;

class OrderFormController extends ShopManagerController
{
    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new OrderFormForm();
                $form->id = \Yii::$app->request->get('id');
                $res = $form->getDetail();

                return $this->asJson($res);
            } else {
                $form = new OrderFormEditForm();
                $form->data = \Yii::$app->request->post('form');
                return $form->save();
            }
        } else {
            return $this->render('setting');
        }
    }

    public function actionList()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderFormForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('list');
        }
    }

    public function actionUpdate()
    {
        $form = new OrderFormUpdate();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    public function actionAllList()
    {
        $form = new OrderFormForm();
        return $this->asJson($form->getAllList());
    }
}
