<?php

namespace app\plugins\hotel\controllers\mall;

use app\plugins\Controller;
use app\plugins\hotel\forms\mall\HotelSettingForm;
use app\plugins\hotel\forms\mall\platform_setting\JinJiangSettingForm;

class PlatformConfigurationController extends Controller
{
    /**
     * 酒店平台配置
     * @return string|\yii\web\Response
     */
    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new HotelSettingForm();
                $param = \Yii::$app->request->get('platformGroup');
                $res = $form->getDetail($param);
                return $this->asJson($res);
            } else {
                $data = \Yii::$app->request->post('ruleForm');
                $jinjiang = new JinJiangSettingForm();
                $res = $jinjiang->set($data);
                return $this->asJson($res);
            }
        } else {
            return $this->render('setting');
        }
    }
}