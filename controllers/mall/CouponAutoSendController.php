<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 营销中心管理-优惠券-自动发放
 * Author: zal
 * Date: 2020-04-18
 * Time: 14:50
 */

namespace app\controllers\mall;

use app\forms\mall\statistics\CouponAutoSendForm;

class CouponAutoSendController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new CouponAutoSendForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    public function actionDestroy()
    {
        if ($id = \Yii::$app->request->post('id')) {
            $form = new CouponAutoSendForm();
            $form->id = $id;
            return $this->asJson($form->destroy());
        } else {
            return $this->asJson([
                'code' => 1,
                'msg' => 'no post'
            ]);
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new CouponAutoSendForm();
            if (\Yii::$app->request->isPost) {
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            } else {
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }
}
