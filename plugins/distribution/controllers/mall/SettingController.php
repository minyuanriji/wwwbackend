<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 16:09
 */

namespace app\plugins\distribution\controllers\mall;


use app\plugins\Controller;
use app\plugins\distribution\forms\mall\DistributionSettingForm;


class SettingController extends Controller
{
    public function actionIndex()
    {

        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {

                $form = new DistributionSettingForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new DistributionSettingForm();
                return $this->asJson($form->search());
            }
        }
        return $this->render('index');
    }


}