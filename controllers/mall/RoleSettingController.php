<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 店铺管理-员工管理-基础设置
 * Author: zal
 * Date: 2020-04-14
 * Time: 17:25
 */

namespace app\controllers\mall;

use app\forms\mall\shop\role_setting\RoleSettingEditForm;
use app\forms\mall\shop\role_setting\RoleSettingForm;

class RoleSettingController extends ShopManagerController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new RoleSettingForm();
                $res = $form->getDetail();

                return $this->asJson($res);
            } else {
                $form = new RoleSettingEditForm();
                $form->data = \Yii::$app->request->post('form');
                return $form->save();
            }
        } else {
            return $this->render('index');
        }
    }
}
