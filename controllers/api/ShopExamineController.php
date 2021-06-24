<?php

namespace app\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\forms\api\store\StoreForm;
use app\plugins\mch\forms\mall\MchEditForm;

class ShopExamineController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ]
        ]);
    }

    /**
     * 店铺审核列表
     * @return array
     */
    public function actionShopList()
    {
        $form = new StoreForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    //门店审核、查看门店
    public function actionDetails()
    {
        if (\Yii::$app->request->isPost) {
            $form = new StoreForm();
            $data = $this->requestData;
            return $this->asJson($form->save($data));
        } else {
            $form = new StoreForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getDetail());
        }
    }
}