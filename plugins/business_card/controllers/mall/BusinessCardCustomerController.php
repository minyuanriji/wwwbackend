<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 客户资料
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 16:09
 */

namespace app\plugins\distribution\controllers\mall;


use app\plugins\Controller;
use app\plugins\distribution\forms\mall\BusinessCardSettingForm;


class BusinessCardCustomerController extends Controller
{
    public function actionIndex()
    {

        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {

                $form = new BusinessCardSettingForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new BusinessCardSettingForm();
                return $this->asJson($form->search());
            }
        }
        return $this->render('index');
    }


}