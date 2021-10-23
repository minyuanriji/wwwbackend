<?php
namespace app\mch\controllers\api;

use app\mch\forms\api\MchSharePosterForm;

/**
 * @deprecated
 */
class ShareController extends MchMApiController {

    /**
     * 分享海报
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionPoster(){
        $shareForm = new MchSharePosterForm([
            'name'   => $this->mchData['store']['name']
        ]);
        $shareForm->attributes = $this->requestData;
        $shareForm->mch_id = $this->mch_id;
        return $shareForm->getSharePoster();
    }

}