<?php
namespace app\mch\controllers\api;


use app\mch\forms\api\mch_set\SetAccountForm;
use app\mch\forms\api\mch_set\SetInfoForm;
use app\mch\forms\api\mch_set\SetPicsForm;

class MchSetController extends MchMApiController{


    /**
     * 设置账号信息
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionSetAccount(){
        $form = new SetAccountForm();
        $form->attributes = $this->requestData;
        $form->mch_id     = $this->mch_id;
        $form->mall_id    = $this->mall_id;

        $this->asJson($form->save());
    }

    /**
     * 设置店铺信息
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionSetInfo(){
        $form = new SetInfoForm();
        $form->attributes = $this->requestData;
        $form->mch_id     = $this->mch_id;

        $this->asJson($form->save());
    }

    /**
     * 设置店铺图片
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionSetPics(){

        $form = new SetPicsForm();
        $form->attributes = $this->requestData;
        $form->mch_id     = $this->mch_id;

        $this->asJson($form->save());

    }

}