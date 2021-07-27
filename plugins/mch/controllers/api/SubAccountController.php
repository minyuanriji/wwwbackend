<?php

namespace app\plugins\mch\controllers\api;


use app\mch\controllers\api\MchMApiController;
use app\plugins\mch\forms\api\MchSubAccountAddForm;
use app\plugins\mch\forms\api\MchSubAccountDeleteForm;
use app\plugins\mch\forms\api\MchSubAccountListForm;

class SubAccountController extends MchMApiController {

    /**
     * 子账号列表
     * @return \yii\web\Response
     */
    public function actionList(){
        $form = new MchSubAccountListForm();
        $form->attributes = $this->requestData;
        $form->mch_id = $this->mch_id;
        return $this->asJson($form->getList());
    }

    /**
     * 删除账号
     * @return \yii\web\Response
     */
    public function actionDelete(){
        $form = new MchSubAccountDeleteForm();
        $form->attributes = $this->requestData;
        $form->mch_id = $this->mch_id;
        return $this->asJson($form->delete());
    }



    /**
     * 添加子账号
     * @return \yii\web\Response
     */
    public function actionAdd(){
        $form = new MchSubAccountAddForm();
        $form->attributes = $this->requestData;
        $form->mch_id = $this->mch_id;
        return $this->asJson($form->save());
    }

}