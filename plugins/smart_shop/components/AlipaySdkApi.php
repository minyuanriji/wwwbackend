<?php

namespace app\plugins\smart_shop\components;

use app\helpers\ArrayHelper;
use yii\base\Component;

class AlipaySdkApi extends Component{

    public $appId;
    public $rsaPrivateKeyPath;
    public $alipayrsaPublicKeyPath;

    private $aop = null;

    public function init(){
        parent::init(); // TODO: Change the autogenerated stub

    }

    public function getAop(){
        if($this->aop == null){
            require_once __DIR__ . "/alipay_sdk/AopClient.php";
            $aop = new \AopClient ();

            $aop->gatewayUrl         = 'https://openapi.alipay.com/gateway.do';
            $aop->appId              = $this->appId;

            $aop->rsaPrivateKey      = trim(file_get_contents($this->rsaPrivateKeyPath));
            $aop->alipayrsaPublicKey = trim(file_get_contents($this->alipayrsaPublicKeyPath));

            $aop->apiVersion         = '1.0';
            $aop->signType           = 'RSA2';
            $aop->postCharset        = 'UTF-8';
            $aop->format             = 'json';
            $this->aop = $aop;
        }
        return $this->aop;
    }

    /**
     * 分账关系绑定接口
     * @param $appAuthToken
     * @param $receiver_list
     * @throws \Exception
     */
    public function tradeRoyaltyRelationBind($appAuthToken, $receiver_list, $out_request_no){
        require_once __DIR__ . "/alipay_sdk/request/AlipayTradeRoyaltyRelationBindRequest.php";

        $request = new \AlipayTradeRoyaltyRelationBindRequest ();
        $request->setBizContent(json_encode([
            "receiver_list"  => $receiver_list,
            "out_request_no" => $out_request_no
        ]));
        $result = $this->getAop()->execute($request, null, $appAuthToken);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            //关系绑定成功
        } else {
            throw new \Exception(isset($result->$responseNode) ? $result->$responseNode->sub_msg : "分账关系绑定失败");
        }
    }

    /**
     * 执行分账操作
     * @param $appAuthToken
     * @param $out_request_no
     * @param $trade_no
     * @param $royalty_parameters
     * @throws \Exception
     */
    public function tradeOrderSettle($appAuthToken, $out_request_no, $trade_no, $royalty_parameters){
        require_once __DIR__ . "/alipay_sdk/request/AlipayTradeOrderSettleRequest.php";

        $option = [
            "out_request_no"     => $out_request_no,
            "trade_no"           => $trade_no,
            "royalty_parameters" => $royalty_parameters
        ];
        $request = new \AlipayTradeOrderSettleRequest ();
        $request->setBizContent(json_encode($option));
        $result = $this->getAop()->execute($request, null, $appAuthToken);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){

        } else {
            throw new \Exception(isset($result->$responseNode) ? $result->$responseNode->sub_msg : "分账失败");
        }

    }

    /**
     * 统一收单线下交易查询
     * @param $params
     * @param $appAuthToken
     */
    public function tradeQuery($params, $appAuthToken = null){
        require_once __DIR__ . "/alipay_sdk/request/AlipayTradeQueryRequest.php";
        $object = new \stdClass();
        $object->out_trade_no = $params['out_trade_no'];
        $json = json_encode($object);
        $request = new \AlipayTradeQueryRequest();
        $request->setBizContent($json);
        $result = $this->getAop()->execute($request, null, $appAuthToken);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        $resultData = [];
        if(!empty($resultCode) && $resultCode == 10000){
            $resultData =isset($result->$responseNode) ? ArrayHelper::toArray($result->$responseNode) : [];
        } else {
            throw new \Exception(isset($responseNode->sub_msg) ? $responseNode->sub_msg : "交易查询查询失败");
        }
        return $resultData;
    }
}