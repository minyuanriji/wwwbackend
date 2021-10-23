<?php

namespace app\commands\alibaba_distribution_goods_task;

use app\plugins\alibaba\models\AlibabaApp;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionGoodsWarn;
use lin010\alibaba\c2b2b\api\GetGoodsDetail;
use lin010\alibaba\c2b2b\api\GetGoodsDetailResponse;
use lin010\alibaba\c2b2b\Distribution;
use yii\base\Action;


class DoWarnAction extends Action{

    /**
     * 阿里巴巴分销订单付款处理任务
     */
    public function run(){
        $this->controller->commandOut(date("Y/m/d H:i:s") . " AlibabaDistributionGoodsTaskController::DoWarnAction start");
        while(true){
            $goodsWarn = AlibabaDistributionGoodsWarn::find()->where([
                "flag" => 0
            ])->orderBy("updated_at ASC")->one();
            if(!$goodsWarn) continue;

            try {
                $aliGoods = AlibabaDistributionGoodsList::findOne($goodsWarn->goods_id);
                if(!$aliGoods){
                    throw new \Exception("商品[ID:{$goodsWarn->goods_id}]不存在");
                }

                $app = AlibabaApp::findOne($aliGoods->app_id);

                $distribution = new Distribution($app->app_key, $app->secret);
                $res = $distribution->requestWithToken(new GetGoodsDetail([
                    "offerId" => $aliGoods->ali_offerId
                ]), $app->access_token);
                if(!$res instanceof GetGoodsDetailResponse){
                    throw new \Exception("[GetGoodsDetailResponse]返回结果异常");
                }
                if($res->error){
                    throw new \Exception($res->error);
                }
                print_r($res->productInfo);
                exit;
            }catch (\Exception $e){
                $this->controller->commandOut($e->getMessage());
            }
        }
    }
}