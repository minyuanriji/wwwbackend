<?php
namespace app\mch\controllers\api;


use app\controllers\api\ApiController;
use app\helpers\APICacheHelper;
use app\mch\forms\api\CatListForm;

class GetMchGoodsCatsController extends ApiController{

    /**
     * 分类列表
     * @return \yii\web\Response
     */
    public function actionIndex(){
        $search = APICacheHelper::get(APICacheHelper::MCH_API_GET_MCH_GOODS_CATS, function($helper){
            $form = new CatListForm();
            $form->attributes = $this->requestData;
            return $helper($form->search());
        });
        return $search;
    }
}