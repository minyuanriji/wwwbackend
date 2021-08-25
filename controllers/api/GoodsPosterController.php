<?php

namespace app\controllers\api;

use app\controllers\api\filters\CheckParentFilter;
use app\controllers\api\filters\LoginFilter;
use app\forms\api\poster\PosterForm;

class GoodsPosterController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }
    /**
     * 商品海报
     * @throws \Exception
     */
    public function actionPoster(){
        $form = new PosterForm();
        $goodsForm = $form->goods();
        $goodsForm->sign = "goods/";
        $goodsForm->goods_id = isset($this->requestData["goods_id"]) ? $this->requestData["goods_id"] : 0;
        return $this->asJson($goodsForm->get());
    }
}