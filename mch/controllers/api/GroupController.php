<?php

namespace app\mch\controllers\api;


use app\plugins\mch\forms\api\mana\MchManaGroupAddItemForm;
use app\plugins\mch\forms\api\mana\MchManaGroupItemListForm;
use app\plugins\mch\forms\api\mana\MchManaRefuseGroupItemListForm;

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

    /**
     * 获取子店不通过列表
     * @return \yii\web\Response
     */
    public function actionRefuseList(){
        $form = new MchManaRefuseGroupItemListForm();
        $form->attributes = $this->requestData;
        $form->mch_id = $this->mch_id;
        return $this->asJson($form->getList());
    }
}