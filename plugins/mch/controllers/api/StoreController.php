<?php
namespace app\plugins\mch\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\core\ApiCode;
use app\helpers\APICacheHelper;
use app\plugins\mch\forms\api\MchStoreDetailForm;
use app\plugins\mch\forms\api\MchStoreListForm;

class StoreController extends ApiController {

    /**
     * 获取商户
     * @return \yii\web\Response
     */
    public function actionList(){

        $form = new MchStoreListForm();
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

    /**
     * 门店详情
     * @return \yii\web\Response
     */
    public function actionDetail(){
        $form = new MchStoreDetailForm();
        $form->attributes = $this->requestData;
        $form->longitude  = ApiController::$commonData['city_data']['longitude'];
        $form->latitude   = ApiController::$commonData['city_data']['latitude'];
        $form->is_login   = !\Yii::$app->user->isGuest;
        $form->login_uid  = $form->is_login ? \Yii::$app->user->id : 0;
        $form->host_info  = \Yii::$app->request->getHostInfo();

        $res = APICacheHelper::get($form, true);
        if($res['code'] == ApiCode::CODE_SUCCESS){
            $res = $res['data'];
        }

        return $this->asJson($res);
    }
}