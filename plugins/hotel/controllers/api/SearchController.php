<?php
namespace app\plugins\hotel\controllers\api;


use app\plugins\ApiController;
use app\plugins\hotel\forms\api\hotel_search\HotelSearchFilterForm;
use app\plugins\hotel\forms\api\hotel_search\HotelSearchPrepareForm;

class SearchController extends ApiController{

    /**
     * 搜索酒店
     * @return \yii\web\Response
     */
    public function actionPrepare(){
        $form = new HotelSearchPrepareForm();
        $form->attributes = $this->requestData;

        if($form->hasData()){
            //return $this->asJson($form->history());
        }

        return $this->asJson($form->prepare());
    }

    /**
     * 查询可预订酒店
     * @return \yii\web\Response
     */
    public function actionFilter(){
        $form = new HotelSearchFilterForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->filter());
    }

}