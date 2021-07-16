<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-28
 * Time: 11:49
 */


namespace app\plugins\mpwx\controllers\mall;


use app\plugins\Controller;
use app\plugins\mpwx\forms\config\ConfigEditForm;
use app\plugins\mpwx\forms\config\ConfigForm;


class ConfigController extends Controller
{
    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new ConfigForm();
                $res = $form->getDetail();
                return $this->asJson($res);
            } else {
                $form = new ConfigEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                return $form->save();
            }
        } else {
            return $this->render('setting');
        }
    }
}
