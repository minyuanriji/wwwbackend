<?php
namespace app\plugins\hotel\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\plugins\ApiController;
use app\plugins\hotel\forms\api\user_center\UserCenterCancelOrderForm;
use app\plugins\hotel\forms\api\user_center\UserCenterOrderDetailForm;
use app\plugins\hotel\forms\api\user_center\UserCenterOrderListForm;
use app\plugins\hotel\forms\api\user_center\UserCenterOrderRefundableForm;
use app\plugins\hotel\forms\api\user_center\UserCenterOrderRefundApplyForm;
use app\plugins\hotel\forms\api\user_center\UserCenterSaveOrderResidentForm;

class UserCenterController extends ApiController{

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ]
        ]);
    }

    /**
     * 取消订单
     * @return \yii\web\Response
     */
    public function actionCancelOrder(){
        $form = new UserCenterCancelOrderForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->save());
    }

    /**
     * 订单列表
     * @return \yii\web\Response
     */
    public function actionOrderList(){
        $form = new UserCenterOrderListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

    /**
     * 订单详情
     * @return \yii\web\Response
     */
    public function actionOrderDetail(){
        $form = new UserCenterOrderDetailForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getDetail());
    }

    /**
     * 订单是否可以退款
     * @return \yii\web\Response
     */
    public function actionOrderRefundable(){
        $form = new UserCenterOrderRefundableForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->get());
    }

    /**
     * 订单申请退款
     * @return \yii\web\Response
     */
    public function actionOrderRefundApply(){
        $form = new UserCenterOrderRefundApplyForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->apply());
    }

    /**
     * 未支付订单修改入住人信息
     * @return \yii\web\Response
     */
    public function actionSaveOrderResident(){
        $form = new UserCenterSaveOrderResidentForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->save());
    }

}