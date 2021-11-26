<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 购物车接口类
 * Author: zal
 * Date: 2020-04-28
 * Time: 16:01
 */

namespace app\controllers\api;

use app\controllers\api\filters\BindMobileFilter;
use app\controllers\api\filters\LoginFilter;
use app\controllers\api\filters\CheckParentFilter;
use app\forms\api\cart\CartAddForm;
use app\forms\api\cart\CartEditForm;
use app\forms\api\cart\CartForm;

class CartController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
            'bindMobile' => [
                'class' => BindMobileFilter::class,
            ],
            'checkParent' => [
                'class' => CheckParentFilter::class,
            ]
        ]);
    }

    /**
     * 购物车列表
     * @Author: zal
     * @Date: 2020-04-27
     * @Time: 10:33
     * @return \yii\web\Response
     */
    public function actionIndex(){
        $cartForm = new CartForm();
        $cartForm->attributes = $this->requestData;
        return $this->asJson($cartForm->getCartList());
    }

    /**
     * 修改购物车
     * @Author: zal
     * @Date: 2020-04-27
     * @Time: 10:33
     * @return \yii\web\Response
     */
    public function actionModify(){
        $cartEditForm = new CartEditForm();
        $cartEditForm->list = isset($this->requestData["list"])?$this->requestData["list"]:$this->requestData;
        return $this->asJson($cartEditForm->modifyCart());
    }

    /**
     * 加入购物车
     * @Author: zal
     * @Date: 2020-04-28
     * @Time: 18:33
     * @return \yii\web\Response
     */
    public function actionAdd(){
        $cartAddForm = new CartAddForm();
        $cartAddForm->attributes = $this->requestData;
        $headers = \Yii::$app->request->headers;
        if(isset($headers["x-stands-mall-id"]) && !empty($headers["x-stands-mall-id"]) && $headers["x-stands-mall-id"] != 5){
            $cartAddForm->mall_id = $headers["x-stands-mall-id"];
        }else{
            $cartAddForm->mall_id = \Yii::$app->mall->id;
        }
        $res = $cartAddForm->addCart();
        return $this->asJson($res);
    }
}