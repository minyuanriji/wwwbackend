<?php
namespace app\mch\controllers\api;


use app\controllers\api\ApiController;
use app\mch\forms\api\CatListForm;

class GetMchGoodsCatsController extends ApiController{

    /**
     * 分类列表
     * @return \yii\web\Response
     */
    public function actionIndex(){
        $form = new CatListForm();
        $form->attributes = $this->requestData;
        return $form->search();
    }
}