<?php

namespace app\plugins\smart_shop\controllers\api;

use app\controllers\api\ApiController;
use app\core\ApiCode;
use app\helpers\APICacheHelper;
use app\plugins\smart_shop\forms\api\ShoppingVoucherGoodsAddForm;
use app\plugins\smart_shop\forms\api\ShoppingVoucherGoodsCategorysForm;
use app\plugins\smart_shop\forms\api\ShoppingVoucherGoodsChoosedForm;
use app\plugins\smart_shop\forms\api\ShoppingVoucherGoodsDeleteForm;
use app\plugins\smart_shop\forms\api\ShoppingVoucherGoodsListForm;

class ShoppingVoucherGoodsController extends ApiController {

    /**
     * 购物券商品列表
     * @return string|\yii\web\Response
     */
    public function actionList(){
        $form = new ShoppingVoucherGoodsListForm();
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
        $form = new ShoppingVoucherGoodsCategorysForm();
        $form->attributes = $this->requestData;
        $form->mall_id    = \Yii::$app->mall->id;

        $res = APICacheHelper::get($form, true);
        if($res['code'] == ApiCode::CODE_SUCCESS){
            $res = $res['data'];
        }

        return $this->asJson($res);
    }

    /**
     * 智慧门店添加购物券商品
     * @return string|\yii\web\Response
     */
    public function actionAdd(){
        $form = new ShoppingVoucherGoodsAddForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->save());
    }

    /**
     * 智慧门店删除购物券商品
     * @return string|\yii\web\Response
     */
    public function actionDelete(){
        $form = new ShoppingVoucherGoodsDeleteForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->delete());
    }

    /**
     * 已选择的购物券商品
     * @return string|\yii\web\Response
     */
    public function actionChoosed(){
        $form = new ShoppingVoucherGoodsChoosedForm();
        $form->attributes = $this->requestData;
        $form->mall_id    = \Yii::$app->mall->id;
        return $this->asJson($form->getList());
    }
}