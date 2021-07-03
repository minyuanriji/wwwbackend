<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 店铺管理-页面管理-页面标题
 * Author: zal
 * Date: 2020-04-14
 * Time: 15:50
 */

namespace app\controllers\mall;

use app\forms\mall\shop\PageTitleEditForm;
use app\forms\mall\shop\PageTitleForm;

class PageTitleController extends ShopManagerController
{
    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new PageTitleForm();
                $res = $form->getList();

                return $this->asJson($res);
            } else {
                $form = new PageTitleEditForm();
                $form->data = \Yii::$app->request->post('list');

                return $form->save();
            }
        } else {
            return $this->render('setting');
        }
    }

    /**
     * 恢复默认
     * @return \yii\web\Response
     */
    public function actionRestoreDefault()
    {
        $form = new PageTitleForm();
        $res = $form->restoreDefault();

        return $this->asJson($res);
    }
}
