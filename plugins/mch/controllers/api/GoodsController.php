<?php

namespace app\plugins\mch\controllers\api;


use app\controllers\api\ApiController;
use app\core\ApiCode;
use app\helpers\APICacheHelper;
use app\plugins\mch\forms\api\MchGoodsListForm;
use app\plugins\mch\forms\api\MchGoodsRecommandListForm;

class GoodsController extends ApiController {

    /**
     * 商品列表
     * @return \yii\web\Response
     */
    public function actionList(){
        $form = new MchGoodsListForm();
        $form->attributes = $this->requestData;
        $form->is_login   = !\Yii::$app->user->isGuest;
        $form->login_uid  = $form->is_login ? \Yii::$app->user->id : 0;
        $form->mall_id    = \Yii::$app->mall->id;

        $res = APICacheHelper::get($form);

        return $this->asJson($res['code'] == ApiCode::CODE_SUCCESS ? $res['data'] : $res);
    }

    /**
     * 获取商户爆品
     * @return \yii\web\Response
     */
    public function actionRecommandList(){

        $form = new MchGoodsRecommandListForm();

        $form->attributes = $this->requestData;
        $form->is_login   = !\Yii::$app->user->isGuest;
        $form->login_uid  = $form->is_login ? \Yii::$app->user->id : 0;

        $res = APICacheHelper::get($form);

        return $this->asJson($res['code'] == ApiCode::CODE_SUCCESS ? $res['data'] : $res);
    }


}
