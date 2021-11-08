<?php

namespace app\plugins\mch\controllers\api\mana;

use app\controllers\api\ApiController;
use app\plugins\mch\controllers\api\mana\filter\AccessFilter;
use app\plugins\mch\controllers\api\mana\filter\LoginFilter;
use app\plugins\mch\forms\api\MchAdminAuthMobileForm;

class MchAdminController extends ApiController {

    public static $adminUser = null;

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => [
                    'plugin/mch/api/mana/mch-admin/auth-mobile',
                ]
            ],
            'access' => [
                'class' => AccessFilter::class,
                'denyRoutes' => [
                    'plugin/mch/api/mana/sub-account/delete',
                    'plugin/mch/api/mana/sub-account/add',
                    'plugin/mch/api/mana/account/set-pwd',
                    'plugin/mch/api/mana/account/validate-mobile',
                    'plugin/mch/api/mana/account/update-mobile',
                    'plugin/mch/api/mana/account/set-withdraw-pwd',
                    'plugin/mch/api/mana/account/set-settle-info',
                    'plugin/mch/api/mana/account/withdraw',
                ]
            ],
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