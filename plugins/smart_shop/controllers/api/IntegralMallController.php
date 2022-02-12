<?php

namespace app\plugins\smart_shop\controllers\api;

use app\controllers\api\ApiController;
use app\core\ApiCode;
use app\forms\api\cat\CatListForm;
use app\helpers\APICacheHelper;
use app\plugins\smart_shop\forms\api\IntegralMallGoodsListForm;

class IntegralMallController extends ApiController {

    /**
     * 获取积分商品
     * @return string|\yii\web\Response
     */
    public function actionGoodsList(){
        $form = new IntegralMallGoodsListForm();
        $form->attributes = $this->requestData;
        $form->mall_id    = \Yii::$app->mall->id;

        $res = APICacheHelper::get($form, true);
        if($res['code'] == ApiCode::CODE_SUCCESS){
            $res = $res['data'];
        }

        return $this->asJson($res);
    }

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