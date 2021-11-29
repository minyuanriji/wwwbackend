<?php

namespace app\plugins\alibaba\forms\api;

use app\core\ApiCode;
use app\forms\api\express\ExpressForm;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\Express;
use app\plugins\alibaba\models\AlibabaApp;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail1688;
use app\plugins\alibaba\models\LogisticCompanyList;
use lin010\alibaba\c2b2b\api\GetLogisticCompanyList;
use lin010\alibaba\c2b2b\api\GetLogisticCompanyListResponse;
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
            /*$app->app_key = "9949219";
            $app->secret = "VCzUExEsGEt";
            $app->access_token = "dc7fde2f-f52c-4547-be45-d7f29012df32";
            $detail1688->ali_order_id = "2127541537039809970";*/

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

            $form = new ExpressForm([
                "mobile"        => $res->result['receiver']['receiverMobile'],
                "express"       => $res->result['logisticsCompanyName'],
                "express_no"    => $res->result['logisticsBillNo'],
                "express_code"  => $this->getExpressCode($distribution, $app->access_token, $res->result['logisticsCompanyName']),
                "customer_name" => $res->result['receiver']['receiverName']
            ]);

            return $form->search();
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }

    /**
     * 查询快递编码
     * @param Distribution $distribution
     * @param $token
     * @param $name
     * @return mixed|null
     */
    private function getExpressCode(Distribution $distribution, $token, $name){
        $expressList = Express::getExpressList();
        if(preg_match("/邮政/", $name)){
            return "EMS";
        }else{
            $pattern = "/(快递|速递|快运|速运)/";
            if(preg_match($pattern, $name, $matches)){
                $tail = $matches[1];
                $str = substr($name, 0, strpos($name, $matches[1]));
                //$str = preg_replace($pattern, "", $name);
                $similars = [];
                foreach($expressList as $item){
                    if(strpos($item['name'], $str) !== FALSE){
                        $similars[] = $item;
                    }
                }
                return !empty($similars) ? $similars[0]['code'] : null;
            }
        }

        return null;
    }

}