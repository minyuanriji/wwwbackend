<?php
namespace app\plugins\mch\controllers\api\mana;

use app\plugins\mch\forms\api\mana\MchManaGroupAddItemForm;
use app\plugins\mch\forms\api\mana\MchManaGroupItemListForm;

class GroupController extends MchAdminController {

    /**
     * 获取子店列表
     * @return \yii\web\Response
     */
    public function actionItemList(){
        $form = new MchManaGroupItemListForm();
        $form->attributes = $this->requestData;
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
        return $this->asJson($form->save());
    }
}