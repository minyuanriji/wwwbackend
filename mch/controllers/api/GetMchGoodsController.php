<?php
namespace app\mch\controllers\api;

use app\controllers\api\ApiController;
use app\helpers\APICacheHelper;
use app\mch\forms\api\GoodsListForm;

class GetMchGoodsController extends ApiController{

    /**
     * 商品列表
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        $list = APICacheHelper::get(APICacheHelper::MCH_API_GET_MCH_GOODS, function($helper){
            $form = new GoodsListForm();
            $form->attributes = $this->requestData;
            return $helper($form->getList());
        });

        return $this->asJson($list);
    }
}