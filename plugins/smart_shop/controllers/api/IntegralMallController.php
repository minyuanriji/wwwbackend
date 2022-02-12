<?php

namespace app\plugins\smart_shop\controllers\api;

use app\controllers\api\ApiController;
use app\core\ApiCode;
use app\forms\api\cat\CatListForm;
use app\helpers\APICacheHelper;

class IntegralMallController extends ApiController {

    /**
     * 获取分类
     * @return string|\yii\web\Response
     */
    public function actionCategorys(){
        $form = new CatListForm();
        $form->attributes = $this->requestData;
        $form->mall_id = \Yii::$app->mall->id;

        $res = APICacheHelper::get($form);
        if($res['code'] == ApiCode::CODE_SUCCESS){
            $res = $res['data'];
        }

        return $this->asJson($res);
    }

}