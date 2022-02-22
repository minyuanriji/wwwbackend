<?php

namespace app\plugins\smart_shop\controllers\api;

use app\controllers\api\ApiController;
use app\core\ApiCode;
use app\helpers\APICacheHelper;
use app\plugins\smart_shop\forms\api\ShopDetailForm;
use app\plugins\smart_shop\forms\api\ShopListForm;

class ShopController extends ApiController {

    /**
     * 智慧门店列表
     * @return string|\yii\web\Response
     */
    public function actionList(){
        $form = new ShopListForm();
        $form->attributes = $this->requestData;
        $form->mall_id    = \Yii::$app->mall->id;
        $form->host_info  = \Yii::$app->request->getHostInfo();

        $res = APICacheHelper::get($form);
        if($res['code'] == ApiCode::CODE_SUCCESS){
            $res = $res['data'];
        }

        return $this->asJson($res);
    }

    /**
     * 智慧门店详情
     * @return string|\yii\web\Response
     */
    public function actionDetail(){
        $form = new ShopDetailForm();
        $form->attributes = $this->requestData;
        $form->host_info  = \Yii::$app->request->getHostInfo();

        $res = APICacheHelper::get($form, true);
        if($res['code'] == ApiCode::CODE_SUCCESS){
            $res = $res['data'];
        }

        return $this->asJson($res);
    }
}