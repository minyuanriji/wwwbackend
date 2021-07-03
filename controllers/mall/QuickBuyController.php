<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 15:04
 */

namespace app\controllers\mall;


class QuickBuyController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new QuickShopCatForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isPost) {
            $form = new QuickShopCatForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }

    public function actionEditSort()
    {
        if (\Yii::$app->request->isPost) {
            $form = new QuickShopCatForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->editSort());
        }
    }

    public function actionDestroy()
    {
        if ($id = \Yii::$app->request->post('id')) {
            $form = new QuickShopCatForm();
            $form->id = $id;
            return $this->asJson($form->destroy());
        }
    }

}