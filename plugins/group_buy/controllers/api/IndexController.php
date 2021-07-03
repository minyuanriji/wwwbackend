<?php

namespace app\plugins\group_buy\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\plugins\ApiController;
use app\plugins\group_buy\forms\mall\GroupBuyGoodsQueryForm;
use app\plugins\group_buy\forms\api\GoodsForm;

class IndexController extends ApiController
{
    public $source = 'front';

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class'  => LoginFilter::class,
                'ignore' => ['get-list', 'detail']
            ],
        ]);
    }

    /**
     * 拼团商品列表
     * @return \yii\web\Response
     */
    public function actionGetList()
    {
        $form             = new GroupBuyGoodsQueryForm();
        $form->attributes = $this->requestData;
        $form->source     = $this->source;
        return $this->asJson($form->queryList());
    }

    /**
     * 拼团商品详情
     * @return \yii\web\Response
     */
    public function actionDetail()
    {
        $form             = new GoodsForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getGroupBuyDetail());
    }
}