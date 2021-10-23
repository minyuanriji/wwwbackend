<?php
namespace app\mch\controllers\api;

use app\controllers\api\ApiController;
use app\core\ApiCode;
use app\helpers\APICacheHelper;
use app\plugins\mch\forms\api\MchGoodsListForm;

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
        $res = APICacheHelper::get($form);
        if($res['code'] == ApiCode::CODE_SUCCESS){
            $res = $res['data'];
        }

        return $this->asJson($res);
    }
}