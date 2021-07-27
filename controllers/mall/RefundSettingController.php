<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 18:39
 */

namespace app\controllers\mall;


use app\forms\mall\refund_setting\RefundAddressEditForm;
use app\forms\mall\refund_setting\RefundAddressForm;
use app\models\Mall;

class RefundSettingController extends MallController
{


    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new RefundAddressForm();
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
                $form = new RefundAddressEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                $res = $form->save();

                return $this->asJson($res);
            } else {
                $form = new RefundAddressForm();
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
    public function actionDelete()
    {
        $form = new RefundAddressForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->delete();
        return $this->asJson($res);
    }

}