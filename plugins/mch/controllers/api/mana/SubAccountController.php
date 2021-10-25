<?php

namespace app\plugins\mch\controllers\api\mana;


use app\plugins\mch\forms\api\mana\MchManaSubAccountAddForm;
use app\plugins\mch\forms\api\mana\MchManaSubAccountDeleteForm;
use app\plugins\mch\forms\api\mana\MchManaSubAccountListForm;

class SubAccountController extends MchAdminController {

    /**
     * 子账号列表
     * @return \yii\web\Response
     */
    public function actionList(){
        $form = new MchManaSubAccountListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

    /**
     * 删除账号
     * @return \yii\web\Response
     */
    public function actionDelete(){
        $form = new MchManaSubAccountDeleteForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->delete());
    }



    /**
     * 添加子账号
     * @return \yii\web\Response
     */
    public function actionAdd(){
        $form = new MchManaSubAccountAddForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->save());
    }

}