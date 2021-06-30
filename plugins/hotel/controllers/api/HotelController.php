<?php
namespace app\plugins\hotel\controllers\api;

use app\plugins\ApiController;
use app\plugins\hotel\forms\api\HotelDetailForm;
use app\plugins\hotel\forms\api\HotelSearchPrepareForm;
use app\plugins\hotel\forms\api\HotelSimpleListForm;

class HotelController extends ApiController{

    /**
     * 酒店列表
     * @return \yii\web\Response
     */
    public function actionSimpleList(){
        $form = new HotelSimpleListForm();
        $form->attributes = $this->requestData;

        if(empty($form->lng) || empty($form->lat)){
            $form->lng = static::$commonData['city_data']['longitude'];
            $form->lat = static::$commonData['city_data']['latitude'];
        }

        return $this->asJson($form->getList());

    }

    /**
     * 酒店信息
     * @return \yii\web\Response
     */
    public function actionDetail(){
        $form = new HotelDetailForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getDetail());
    }
}