<?php
namespace app\plugins\mch\controllers\api\mana;

use app\plugins\mch\forms\api\mana\MchManaInfoBaseForm;
use app\plugins\mch\forms\api\mana\MchManaInfoPosterForm;

class InfoController extends MchAdminController {

    /**
     * 获取基本信息
     * @return \yii\web\Response
     */
    public function actionBase(){
        $form = new MchManaInfoBaseForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->get());
    }

    /**
     * 分享海报
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionPoster(){
        $shareForm = new MchManaInfoPosterForm();
        $shareForm->attributes = $this->requestData;
        return $shareForm->getSharePoster();
    }
}