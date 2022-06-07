<?php

namespace app\plugins\smart_shop\controllers\api;

use app\controllers\api\ApiController;
use app\plugins\smart_shop\components\SmartShopKPI;

class KpiController extends ApiController {

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
     * 分享优惠券链接访问统计
     * @return \yii\web\Response
     */
    public function actionLinkCouponDetail(){
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