<?php
namespace app\mch\controllers\api;


use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\core\ApiCode;
use app\mch\forms\api\MchApplyForm;

class MchApplyController extends ApiController{

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ]
        ]);
    }

    public function actionSetting(){
        $agreement = @file_get_contents(\Yii::getAlias("@runtime/agreement"));
        $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'agreement' => $agreement ? $agreement : ""
            ]
        ]);
    }

    public function actionIndex(){
        $form = new MchApplyForm();
        $form->attributes = \Yii::$app->request->post();
        $form->user_id    = \Yii::$app->user->id;
        //$form->realname   = \Yii::$app->user->identity->realname;
        //$form->username   = \Yii::$app->user->identity->username;

        $this->asJson($form->save());
    }
}