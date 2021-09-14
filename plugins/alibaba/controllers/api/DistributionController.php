<?php

namespace app\plugins\alibaba\controllers\api;

use app\controllers\api\ApiController;
use app\core\ApiCode;
use app\helpers\APICacheHelper;
use app\plugins\alibaba\forms\api\AlibabaDistributionGetCategoryForm;

class DistributionController extends ApiController {

    /**
     * 获取分类
     * @return \yii\web\Response
     */
    public function actionGetCategory(){

        $form = new AlibabaDistributionGetCategoryForm();
        $form->attributes = $this->requestData;
        $form->mall_id = \Yii::$app->mall->id;

        $res = APICacheHelper::get($form);
        if($res['code'] == ApiCode::CODE_SUCCESS){
            $res = $res['data'];
        }

        return $this->asJson($res);
    }

}