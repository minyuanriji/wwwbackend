<?php
namespace app\plugins\hotel\controllers\api;

use app\core\ApiCode;
use app\helpers\APICacheHelper;
use app\plugins\ApiController;
use app\plugins\hotel\forms\api\CityListForm;
use app\plugins\hotel\forms\api\HotelDetailForm;
use app\plugins\hotel\forms\api\HotelDiagramForm;
use app\plugins\hotel\forms\api\HotelInfoForm;
use app\plugins\hotel\forms\api\HotelSearchPrepareForm;
use app\plugins\hotel\forms\api\HotelSimpleListForm;
use app\plugins\hotel\libs\bestwehotel\Config;

class HotelController extends ApiController{

    /**
     * 酒店列表
     * @return \yii\web\Response
     */
    public function actionSimpleList(){
        $form = new HotelSimpleListForm();
        if(!empty($this->requestData['search_id'])){
            $searchData = \Yii::$app->getCache()->get("hotel:" . $this->requestData['search_id']);
            $form->attributes = $searchData;
        }else{
            $form->attributes = $this->requestData;
        }

        if(empty($form->lng) || empty($form->lat)){
            $form->lng = static::$commonData['city_data']['longitude'];
            $form->lat = static::$commonData['city_data']['latitude'];
        }

        $res = APICacheHelper::get($form, true);
        if($res['code'] == ApiCode::CODE_SUCCESS){
            $res = $res['data'];
        }

        return $this->asJson($res);

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

    /*
     * 获取酒店首页轮播图、城市等数据
     * */
    public function actionHotelInfo ()
    {
        $form = new HotelInfoForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getInfo());
    }

    /*
     * 获取城市列表
     * */
    public function actionCityList ()
    {
        $form = new CityListForm();
        return $this->asJson($form->getList());
    }

    /*
     * 获取酒店首页轮播图、城市等数据
     * */
    public function actionHotelDiagram ()
    {
        $form = new HotelDiagramForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getDiagram());
    }
}