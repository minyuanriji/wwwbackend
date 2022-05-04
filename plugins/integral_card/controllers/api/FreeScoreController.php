<?php

namespace app\plugins\integral_card\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\plugins\integral_card\controllers\ApiBaseController;
use app\plugins\integral_card\forms\api\FreeScorePosterForm;
use app\plugins\integral_card\forms\api\FreeScoreTakeForm;

class FreeScoreController extends ApiBaseController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    /**
     * 领取积分
     * @return bool|string|\yii\web\Response
     */
    public function actionTake(){
        $form = new FreeScoreTakeForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->take());
    }
}