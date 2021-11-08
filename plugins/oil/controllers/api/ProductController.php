<?php

namespace app\plugins\oil\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\oil\forms\api\OilProductListForm;

class ProductController extends ApiController {

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ]
        ]);
    }

    /**
     * 获取加油产品
     * @return \yii\web\Response
     */
    public function actionList(){
        $form = new OilProductListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

}