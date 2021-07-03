<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 店铺管理-店铺设计-全局颜色设置
 * Author: zal
 * Date: 2020-04-13
 * Time: 14:50
 */

namespace app\controllers\mall;

use app\forms\mall\shop\ColorEditForm;
use app\forms\mall\shop\ColorForm;

class ColorController extends ShopManagerController
{
    /**
     * 设置
     * @return array|string|\yii\web\Response
     */
    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new ColorForm();
                $res = $form->getDetail();
                return $this->asJson($res);
            } else {
                $form = new ColorEditForm();
                $form->data = \Yii::$app->request->post('form');
                return $form->save();
            }
        }
        return $this->render('setting');
    }

    /**
     * 恢复默认
     * @return \yii\web\Response
     */
    public function actionDefault()
    {
        $form = new ColorForm();
        $res = $form->restoreDefault();
        return $this->asJson($res);
    }
}
