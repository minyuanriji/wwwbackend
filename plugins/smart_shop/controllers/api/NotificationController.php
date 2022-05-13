<?php

namespace app\plugins\smart_shop\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\smart_shop\forms\api\NotificationSetWechatTemplateForm;

class NotificationController extends ApiController {

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => []
            ],
        ]);
    }

    /**
     * 设置微信公众号通知
     * @return \yii\web\Response
     */
    public function actionSetWechatTemplate(){
        $form = new NotificationSetWechatTemplateForm();
        $form->attributes  = $this->requestData;
        return $this->asJson($form->set());
    }
}