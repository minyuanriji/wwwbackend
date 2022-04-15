<?php
namespace app\plugins\mch\controllers\api;

use app\controllers\api\ApiController;
use app\core\ApiCode;
use app\helpers\APICacheHelper;
use app\plugins\mch\forms\api\MchRecommandMchDataForm;

class RecommandController extends ApiController {

    public function actionMchData(){
        $form = new MchRecommandMchDataForm();
        $form->attributes = $this->requestData;
        $form->longitude  = ApiController::$commonData['city_data']['longitude'];
        $form->latitude   = ApiController::$commonData['city_data']['latitude'];
        $form->is_login   = !\Yii::$app->user->isGuest;
        $form->login_uid  = $form->is_login ? \Yii::$app->user->id : 0;
        $form->host_info  = \Yii::$app->request->getHostInfo();

        $res = APICacheHelper::get($form);
        if($res['code'] == ApiCode::CODE_SUCCESS){
            $res = $res['data'];
        }

        return $this->asJson($res);
    }

}