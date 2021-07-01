<?php
namespace app\plugins\hotel\controllers\api;


use app\core\ApiCode;
use app\plugins\ApiController;
use app\plugins\hotel\forms\api\hotel_search\HotelSearchFilterForm;
use app\plugins\hotel\forms\api\hotel_search\HotelSearchPrepareForm;
use app\plugins\hotel\jobs\HotelSearchPrepareJob;

class SearchController extends ApiController{



    /**
     * 搜索酒店
     * @return \yii\web\Response
     */
    public function actionPrepare(){

        $form = new HotelSearchPrepareForm();
        $form->attributes = $this->requestData;
        $form->host_info  = \Yii::$app->getRequest()->getHostInfo();

        if((defined('ENV') && ENV == "pro")){
            $history = $form->history();
            if($history['data']['history'] == 1){
                if($history['data']['is_expired']){
                    \Yii::$app->queue->delay(0)->push(new HotelSearchPrepareJob([
                        "mall_id" => \Yii::$app->mall->id,
                        "form"    => $form
                    ]));
                }
                return $this->asJson($history);
            }
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

        $res = $form->filter();

        return $this->asJson($res);
    }

}