<?php

namespace app\plugins\alibaba\forms\api;

use app\core\ApiCode;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaApp;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail1688;
use lin010\alibaba\c2b2b\api\GetLogisticsInfo;
use lin010\alibaba\c2b2b\api\GetLogisticsInfoResponse;
use lin010\alibaba\c2b2b\Distribution;

class AlibabaDistributionOrderLogisticsForm extends BaseModel{

    public $id_1688;

    public function rules(){
        return [
            [['id_1688'], 'required'],
            [['id_1688'], 'integer'],
        ];
    }

    public function get(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $detail1688 = AlibabaDistributionOrderDetail1688::findOne($this->id_1688);
            if(!$detail1688){
                throw new \Exception('订单不存在');
            }


            $app = AlibabaApp::findOne($detail1688->app_id);
            $distribution = new Distribution($app->app_key, $app->secret);
            $res = $distribution->requestWithToken(new GetLogisticsInfo([
                "webSite" => "1688",
                "orderId" => $detail1688->ali_order_id
            ]), $app->access_token);
            if(!empty($res->error)){
                throw new \Exception($res->error);
            }
            if(!$res instanceof GetLogisticsInfoResponse){
                throw new \Exception("[GetLogisticsInfoResponse]返回结果异常");
            }
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }


}