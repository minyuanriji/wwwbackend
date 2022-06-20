<?php

namespace app\plugins\smart_shop\controllers\api;

use app\controllers\api\ApiController;
use app\plugins\smart_shop\components\SmartShopKPI;
use app\plugins\smart_shop\forms\api\KpiAwardLogForm;

class KpiController extends ApiController {

    /**
     * 获取KPI奖励记录
     * @return \yii\web\Response
     */
    public function actionAwardLog(){
        $form = new KpiAwardLogForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

    /**
     * 分享商品链接访问统计
     * @return \yii\web\Response
     */
    public function actionLinkGoodsDetail(){
        try {
            $kpi = new SmartShopKPI();
            if(!$kpi->linkGoodsDetail($this->requestData)){
                throw new \Exception($kpi->getError());
            }
            return $this->success();
        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }
    }

    /**
     * @deprecated
     * 领取优惠券统计
     * @return \yii\web\Response
     */
    public function actionTakeCoupon(){
        try {
            /*$kpi = new SmartShopKPI();
            if(!$kpi->takeCoupon($this->requestData)){
                throw new \Exception($kpi->getError());
            }*/
            return $this->success();
        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }
    }

    /**
     * 分享优惠券链接访问统计
     * @return \yii\web\Response
     */
    public function actionLinkCouponList(){
        try {
            $kpi = new SmartShopKPI();
            if(!$kpi->linkCouponList($this->requestData)){
                throw new \Exception($kpi->getError());
            }
            return $this->success();
        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }
    }

}