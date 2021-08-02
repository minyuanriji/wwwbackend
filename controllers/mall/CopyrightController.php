<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 店铺管理-页面管理-版权设置
 * Author: zal
 * Date: 2020-04-14
 * Time: 14:50
 */

namespace app\controllers\mall;

use app\forms\mall\shop\CopyrightEditForm;
use app\forms\mall\shop\CopyrightForm;

class CopyrightController extends ShopManagerController
{
    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new CopyrightForm();
                $res = $form->getDetail();

                return $this->asJson($res);
            } else {
                $form = new CopyrightEditForm();
                $form->data = \Yii::$app->request->post('form');
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            }
        } else {
            return $this->render('setting');
        }
    }
}
