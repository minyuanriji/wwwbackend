<?php
namespace app\mch\controllers\api;

use app\controllers\api\ApiController;
use app\helpers\APICacheHelper;
use app\mch\forms\api\MchGoodsListForm;

/**
 * @deprecated
 */
class GetMchGoodsController extends ApiController{

    /**
     * 商品列表
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        $form = new MchGoodsListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }
}