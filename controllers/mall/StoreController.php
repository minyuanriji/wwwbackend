<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 店铺管理-内容管理-门店管理
 * Author: zal
 * Date: 2020-04-14
 * Time: 16:57
 */

namespace app\controllers\mall;

use app\forms\mall\shop\StoreEditForm;
use app\forms\mall\shop\StoreForm;

class StoreController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new StoreForm();
                $form->attributes = \Yii::$app->request->get();
                $list = $form->getList();

                return $this->asJson($list);
            }
        } else {
            return $this->render('index');
        }
    }

    /**
     * 添加、编辑
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
                $form = new StoreForm();
                $form->attributes = \Yii::$app->request->get();
                $detail = $form->getDetail();

                return $this->asJson($detail);
            }
        } else {
            return $this->render('edit');
        }
    }


    /**
     * 删除
     * @return \yii\web\Response
     */
    public function actionDestroy()
    {
        $form = new StoreForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->destroy();

        return $this->asJson($res);
    }

    /**
     * 默认门店
     */
    public function actionSwitchDefault()
    {
        $form = new StoreForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->switchDefault();

        return $this->asJson($res);
    }
}
