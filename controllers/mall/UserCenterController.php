<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 店铺管理-店铺设计-用户中心
 * Author: zal
 * Date: 2020-04-13
 * Time: 16:52
 */

namespace app\controllers\mall;

use app\forms\mall\shop\UserCenterEditForm;
use app\forms\mall\shop\UserCenterForm;

class UserCenterController extends ShopManagerController
{
    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new UserCenterForm();
                $res = $form->getDetail();

                return $this->asJson($res);
            } else {
                $form = new UserCenterEditForm();
                $form->data = \Yii::$app->request->post('form');
                return $form->save();
            }
        } else {
            return $this->render('setting');
        }
    }

    public function actionResetDefault()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new UserCenterEditForm();
            return $form->reset();
        }
    }
}
