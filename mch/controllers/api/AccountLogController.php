<?php
namespace app\mch\controllers\api;

use app\mch\forms\api\MchAccountLogListForm;

/**
 * @deprecated
 */
class AccountLogController extends MchMApiController {


    /**
     * 账户明细列表
     * @return array
     * @throws \Exception
     */
    public function actionList(){
        $form = new MchAccountLogListForm();
        $form->attributes = $this->requestData;
        $form->mch_id = $this->mch_id;
        return $form->getList();
    }
}