<?php
namespace app\mch\controllers\api;

use app\controllers\api\ApiController;
use app\mch\forms\api\GoodsListForm;

class GetMchGoodsController extends ApiController{

    /**
     * 商品列表
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        $form = new GoodsListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }
}