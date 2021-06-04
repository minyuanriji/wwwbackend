<?php
namespace app\plugins\baopin\controllers\api;

use app\helpers\CacheHelper;
use app\plugins\ApiController;
use app\plugins\baopin\forms\api\SearchForm;

class GoodsController extends ApiController{

    /**
     * 搜索爆品库
     * @return \yii\web\Response
     */
    public function actionSearch(){
        $search = CacheHelper::get(CacheHelper::PLUGIN_BAOPIN_API_GOODS_SEARCH, function($helper){
            $form = new SearchForm();
            $form->attributes = $this->requestData;
            return $helper($form->search());
        });
        return $this->asJson($search);
    }

}