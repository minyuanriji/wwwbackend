<?php

namespace app\plugins\alibaba\controllers\api;

use app\controllers\api\ApiController;
use app\core\ApiCode;
use app\helpers\APICacheHelper;
use app\plugins\alibaba\forms\api\AlibabaDistributionGetCategoryForm;
use app\plugins\alibaba\forms\api\AlibabaDistributionGoodsDetailForm;
use app\plugins\alibaba\forms\api\AlibabaDistributionSearchGoodsForm;

class DistributionController extends ApiController {

    /**
     * 获取商品详情
     * @return \yii\web\Response
     */
    public function actionDetail(){
        $form = new AlibabaDistributionGoodsDetailForm();
        $form->attributes = $this->requestData;
        $form->mall_id = \Yii::$app->mall->id;
        $form->user_id = !\Yii::$app->user->isGuest ? \Yii::$app->user->id : 0;

        $res = APICacheHelper::get($form, true);
        if($res['code'] == ApiCode::CODE_SUCCESS){
            $res = $res['data'];
        }

        return $this->asJson($res);
    }

    /**
     * 搜索商品
     * @return \yii\web\Response
     */
    public function actionSearchGoods(){
        $form = new AlibabaDistributionSearchGoodsForm();
        $form->attributes = $this->requestData;
        $form->mall_id = \Yii::$app->mall->id;
        $form->user_id = !\Yii::$app->user->isGuest ? \Yii::$app->user->id : 0;

        $res = APICacheHelper::get($form, true);
        if($res['code'] == ApiCode::CODE_SUCCESS){
            $res = $res['data'];
        }

        return $this->asJson($res);
    }

    /**
     * 获取分类
     * @return \yii\web\Response
     */
    public function actionGetCategory(){

        $form = new AlibabaDistributionGetCategoryForm();
        $form->attributes = $this->requestData;
        $form->mall_id = \Yii::$app->mall->id;
        $form->host_info = \Yii::$app->getRequest()->getHostInfo();

        $res = APICacheHelper::get($form);
        if($res['code'] == ApiCode::CODE_SUCCESS){
            $res = $res['data'];
        }

        return $this->asJson($res);
    }

}