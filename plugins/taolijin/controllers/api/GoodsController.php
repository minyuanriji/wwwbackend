<?php
namespace app\plugins\taolijin\controllers\api;

use app\plugins\ApiController;
use app\plugins\taolijin\forms\api\TaolijinGoodsDetailForm;
use app\plugins\taolijin\forms\api\TaolijinGoodsSearchForm;

class GoodsController extends ApiController{

    /**
     * 获取商品
     * @return \yii\web\Response
     */
    public function actionSearch(){
        $form = new TaolijinGoodsSearchForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->get());
    }

    /**
     * 获取商品详情
     * @return \yii\web\Response
     */
    public function actionDetail(){
        $form = new TaolijinGoodsDetailForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->detail());
    }
}