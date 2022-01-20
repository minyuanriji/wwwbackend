<?php
namespace app\plugins\smart_shop\controllers\api;

use app\controllers\api\ApiController;
use app\plugins\smart_shop\forms\api\EcardGoodsListForm;

class EcardGoodsController extends ApiController {

    /**
     * 积分商城商品列表
     * @return string|\yii\web\Response
     */
    public function actionList(){
        $form = new EcardGoodsListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

}