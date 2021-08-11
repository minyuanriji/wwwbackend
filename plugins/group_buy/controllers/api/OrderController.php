<?php

namespace app\plugins\group_buy\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\logic\OrderLogic;
use app\plugins\group_buy\forms\api\OrderSubmitForm;
use app\plugins\ApiController;
use app\plugins\group_buy\forms\api\OrderForm;

class OrderController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                //'ignore' => ['get-list', 'detail']
            ],
        ]);
    }

    /**
     * 订单预览接口
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\db\Exception
     */
    public function actionToSubmitOrder()
    {
        $form            = new OrderSubmitForm();
        $form->form_data = $this->requestData;
        $result          = $form->toSubmitOrder();
        return $this->asJson($result);
    }

    /**
     * 提交订单
     * xuyaoxiang
     * 2020/08/31
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionDoSubmitOrder()
    {
        $form             = new OrderSubmitForm();
        $form->form_data  = $this->requestData;
        $mallPaymentTypes = OrderLogic::getPaymentTypeConfig();
        return $this->asJson($form->setSupportPayTypes($mallPaymentTypes)->doSubmitOrder());
    }


    /**
     * 订单列表
     * Author: xuyaoxiang
     * @Date: 2020-09-12
     * @Time: 20:33
     * @return array
     * @throws \Exception
     */
    public function actionList()
    {
        $form             = new OrderForm();
        $form->attributes = $this->requestData;
        return $form->getOrderList();
    }
}
