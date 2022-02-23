<?php
namespace app\plugins\mch\controllers\api;


use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\mch\forms\api\MchApplyEasyForm;
use app\plugins\mch\forms\common\apply\MchApplyBasicForm;
use app\plugins\mch\forms\common\apply\MchApplyInfoForm;
use app\plugins\mch\forms\common\apply\MchApplyLicenseForm;
use app\plugins\mch\forms\common\apply\MchApplyResetForm;

class ApplyController extends ApiController{

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ]
        ]);
    }

    /**
     * 店铺入驻申请简单处理
     * @return \yii\web\Response
     */
    public function actionEasy(){
        $form = new MchApplyEasyForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->save());
    }

    /**
     * 店铺入驻申请第一步 - 填写基本信息
     * @return \yii\web\Response
     */
    public function actionBasic(){
        $form = new MchApplyBasicForm();
        $form->attributes = $this->requestData;
        $form->user_id = \Yii::$app->user->id;
        return $this->asJson($form->save());
    }

    /**
     * 店铺入驻申请第二步 - 设置执照信息
     * @return \yii\web\Response
     */
    public function actionLicense(){
        $form = new MchApplyLicenseForm();
        $form->attributes = $this->requestData;
        $form->user_id = \Yii::$app->user->id;
        return $this->asJson($form->save());
    }

    /**
     * 获取申请信息
     * @return \yii\web\Response
     */
    public function actionInfo(){
        $form = new MchApplyInfoForm();
        $form->attributes = $this->requestData;
        $form->user_id = \Yii::$app->user->id;
        return $this->asJson($form->get());
    }

    /**
     * 重置申请状态
     * @return \yii\web\Response
     */
    public function actionReset(){
        $form = new MchApplyResetForm();
        $form->attributes = $this->requestData;
        $form->user_id = \Yii::$app->user->id;
        return $this->asJson($form->reset());
    }
}