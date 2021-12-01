<?php

namespace app\plugins\addcredit\plateform\sdk\jing36;

use app\plugins\addcredit\plateform\result\QueryResult;

class QueryAction extends BaseAction {

    public function run(){
        $configs = $this->getPlateConfig();

        $req = new Req($configs['host'], $configs['app_key'], $configs['app_secret']);
        $params['orderId'] = $this->orderModel->order_no;
        $res = $req->doPost("/v1/mobile/query", $params);

        $queryResult = new QueryResult();
        $queryResult->request_data     = $res['request_data'];
        $queryResult->response_content = $res['response_content'];
        $queryResult->code             = $res['code'] == Code::SUCCESS ? QueryResult::CODE_SUCC : QueryResult::CODE_FAIL;
        $queryResult->message          = $res['message'];
        if($queryResult->code == QueryResult::CODE_SUCC){
            if(isset($res['data']['status'])){
                if($res['data']['status'] == "SUCCESS"){
                    $queryResult->status = "success";
                }elseif($res['data']['status'] == "WAIT"){
                    $queryResult->status = "waiting";
                }else{
                    $queryResult->status = "fail";
                }
            }else{
                $queryResult->status = "waiting";
            }
        }

        return $queryResult;
    }
}