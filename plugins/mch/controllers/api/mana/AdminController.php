<?php

namespace app\plugins\mch\controllers\api\mana;

use app\controllers\api\ApiController;
use app\plugins\mch\controllers\api\mana\filter\LoginFilter;
use app\plugins\mch\forms\api\MchAdminAuthMobileForm;

class AdminController extends ApiController {

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => [
                    'app/plugins/mch/controllers/api/mana/auth-mobile',
                ]
            ]
        ]);
    }

    /**
     * 通过手机号登录
     * @return yii\web\Response
     * @throws \Exception
     */
    public function actionAuthMobile(){
        $form = new MchAdminAuthMobileForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->login());
    }

}