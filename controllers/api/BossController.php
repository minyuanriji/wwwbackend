<?php

namespace app\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\forms\api\boss\BonusListForm;

class BossController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ]
        ]);
    }

    /**
     * 分红明细
     * @return array
     */
    public function actionBonusDetails()
    {
        $form = new BonusListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->details());
    }
}