<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 店铺管理-店铺设计-自定义海报
 * Author: zal
 * Date: 2020-04-14
 * Time: 11:50
 */

namespace app\controllers\mall;

use app\forms\mall\shop\PosterEditForm;
use app\forms\mall\shop\PosterForm;

class PosterController extends ShopManagerController
{
    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new PosterForm();
                $res = $form->getDetail();

                return $this->asJson($res);
            } else {
                $form = new PosterEditForm();
                $form->data = \Yii::$app->request->post('form');
                return $form->save();
            }
        }

        return $this->render('setting');
    }
}
