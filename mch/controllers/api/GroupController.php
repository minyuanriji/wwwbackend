<?php

namespace app\mch\controllers\api;


use app\plugins\mch\forms\api\mana\MchManaGroupAddItemForm;
use app\plugins\mch\forms\api\mana\MchManaGroupItemListForm;

class GroupController extends MchMApiController{

    /**
     * 获取子店列表
     * @return \yii\web\Response
     */
    public function actionItemList(){
        $form = new MchManaGroupItemListForm();
        $form->attributes = $this->requestData;
        $form->mch_id     = $this->mch_id;
        $form->host_info  = \Yii::$app->request->getHostInfo();
        return $this->asJson($form->getList());
    }

    /**
     * 添加子门店
     * @return \yii\web\Response
     */
    public function actionAddItem(){
        $form = new MchManaGroupAddItemForm();
        $form->attributes = $this->requestData;
        $form->mch_id = $this->mch_id;
        return $this->asJson($form->save());
    }
}