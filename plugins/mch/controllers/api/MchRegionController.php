<?php

namespace app\plugins\mch\controllers\api;

use app\plugins\mch\forms\api\MchGetCityLowerForm;

class MchRegionController extends ApiController
{
    //获取市下级地区列表
    public function actionGetCityLower()
    {
        $form = new MchGetCityLowerForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getRegion());
    }
}
