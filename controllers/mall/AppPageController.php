<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 店铺管理-页面管理-小程序页面
 * Author: zal
 * Date: 2020-04-14
 * Time: 14:50
 */

namespace app\controllers\mall;

use app\forms\common\PickLinkForm;
use app\forms\mall\shop\AppPageForm;

class AppPageController extends ShopManagerController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new PickLinkForm();
                $res = $form->appPage();
                return $this->asJson($res);
            } else {
            }
        } else {
            return $this->render('index');
        }
    }

    public function actionQrcode()
    {
        $form = new AppPageForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }
}
