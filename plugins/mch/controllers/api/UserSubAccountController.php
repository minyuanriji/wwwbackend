<?php

namespace app\plugins\mch\controllers\api;


use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\mch\forms\api\UserSubAccountMchListForm;


class UserSubAccountController extends ApiController{

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    /**
     * 用户子账号管理的店铺
     * @return \yii\web\Response
     */
    public function actionMchList(){
        $form = new UserSubAccountMchListForm();
        $form->attributes = $this->requestData;
        $form->user_id = \Yii::$app->user->id;
        return $this->asJson($form->getList());
    }


}