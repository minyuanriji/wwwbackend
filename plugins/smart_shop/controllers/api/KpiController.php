<?php

namespace app\plugins\smart_shop\controllers\api;

use app\controllers\api\ApiController;
use app\plugins\smart_shop\components\SmartShopKPI;

class KpiController extends ApiController {

    /**
     * 分享链接访问统计
     * @return \yii\web\Response
     */
    public function actionLinkGoodsDetail(){
        try {
            $kpi = new SmartShopKPI();
            $kpi->linkGoodsDetail($this->requestData);
            return $this->success();
        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }
    }

}