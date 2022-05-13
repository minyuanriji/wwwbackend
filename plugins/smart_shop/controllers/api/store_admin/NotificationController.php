<?php

namespace app\plugins\smart_shop\controllers\api\store_admin;

use app\plugins\smart_shop\controllers\api\AdminAuthController;
use app\plugins\smart_shop\forms\api\store_admin\NotificationDetailForm;
use app\plugins\smart_shop\forms\api\store_admin\NotificationSetWechatTemplateForm;

class NotificationController extends AdminAuthController {

    /**
     * 获取下单提醒配置数据
     * @return \yii\web\Response
     */
    public function actionOrder(){
        $form = new NotificationDetailForm();
        $form->attributes  = $this->requestData;
        $form->merchant_id = $this->merchant ? $this->merchant['id'] : 0;
        $form->store_id    = $this->store ? $this->store['id'] : '';
        return $this->asJson($form->get());
    }

    /**
     * 设置微信公众号通知数据
     * @return \yii\web\Response
     */
    public function actionSetWechatTemplate(){
        $form = new NotificationSetWechatTemplateForm();
        $form->attributes  = $this->requestData;
        $form->merchant_id = $this->merchant ? $this->merchant['id'] : 0;
        $form->store_id    = $this->store ? $this->store['id'] : '';
        return $this->asJson($form->set());
    }
}