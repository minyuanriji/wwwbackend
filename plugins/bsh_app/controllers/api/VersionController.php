<?php
namespace app\plugins\bsh_app\controllers\api;

use app\controllers\api\ApiController;
use app\plugins\bsh_app\forms\api\AppVersionDetailForm;

class VersionController extends ApiController {

    /**
     * 获取版本信息
     * @return yii\web\Response
     * @throws \Exception
     */
    public function actionDetail(){
        $form = new AppVersionDetailForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getDetail());
    }
}