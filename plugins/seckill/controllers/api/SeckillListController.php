<?php

namespace app\plugins\seckill\controllers\api;

use app\plugins\seckill\forms\api\index\SeckillListForm;
use app\plugins\ApiController;

class SeckillListController extends ApiController
{
    /**
     * 秒杀列表
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        $form = new SeckillListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->list());
    }

}