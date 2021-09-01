<?php
namespace app\plugins\shopping_voucher\controllers\api;

use app\core\ApiCode;
use app\helpers\APICacheHelper;
use app\plugins\ApiController;
use app\plugins\shopping_voucher\forms\api\SearchForm;

class GoodsController extends ApiController{

    /**
     * 搜索爆品库
     * @return \yii\web\Response
     */
    public function actionSearch(){
        $form = new SearchForm();
        $form->attributes = $this->requestData;
        $form->is_login   = !\Yii::$app->user->isGuest;
        $form->login_uid  = $form->is_login ? \Yii::$app->user->id : 0;

        $res = APICacheHelper::get($form);
        if($res['code'] == ApiCode::CODE_SUCCESS){
            $res = $res['data'];
        }

        return $this->asJson($res);
    }

}