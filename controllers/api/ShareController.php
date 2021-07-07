<?php


namespace app\controllers\api;


use app\forms\api\share\InviterInfoForm;

class ShareController extends ApiController{

    /**
     * 获取推荐人信息
     * @return array
     */
    public function actionInviterInfo(){
        $form = new InviterInfoForm();
        $form->attributes = $this->requestData;
        return $form->getDetail();
    }

}