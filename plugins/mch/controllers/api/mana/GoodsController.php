<?php

namespace app\plugins\mch\controllers\api\mana;

use app\core\ApiCode;
use app\helpers\APICacheHelper;
use app\plugins\mch\forms\api\mana\MchManaGoodsListForm;

class GoodsController extends MchAdminController {

    /**
     * 商品列表
     * @return \yii\web\Response
     */
    public function actionList()
    {
        $form = new MchManaGoodsListForm();
        $form->attributes = $this->requestData;
        $form->mch_id = static::$adminUser['mch_id'];

        $res = APICacheHelper::get($form);
        if($res['code'] == ApiCode::CODE_SUCCESS){
            $res = $res['data'];
        }

        return $this->asJson($res);
    }

}