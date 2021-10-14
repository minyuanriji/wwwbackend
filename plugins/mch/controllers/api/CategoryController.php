<?php
namespace app\plugins\mch\controllers\api;


use app\controllers\api\ApiController;
use app\core\ApiCode;
use app\helpers\APICacheHelper;
use app\plugins\mch\forms\api\MchCategoryListForm;

class CategoryController extends ApiController {

    public function actionList(){
        $form = new MchCategoryListForm();

        $form->attributes = \Yii::$app->request->get();
        $form->is_login   = !\Yii::$app->user->isGuest;
        $form->login_uid  = $form->is_login ? \Yii::$app->user->id : 0;

        $res = APICacheHelper::get($form);
        if($res['code'] == ApiCode::CODE_SUCCESS){
            $res = $res['data'];
        }

        return $this->asJson($res);
    }

}