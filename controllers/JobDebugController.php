<?php
namespace app\controllers;



use lin010\alibaba\c2b2b\api\GetGoodsListForUserChoosed;
use lin010\alibaba\c2b2b\Distribution;
use lin010\alibaba\c2b2b\WebOauth2;

class JobDebugController extends BaseController {

    public function actionIndex(){

        $distribution = new Distribution("1265913", "twWQgEYSoiKU");
        $distribution->requestWithToken(new GetGoodsListForUserChoosed([
            "pageNo" => 1,
            "pageSize" => 100
        ]), "d414dce3-67d6-44be-8a0d-d3f773d73e2f2");

        /*$auth = new WebOauth2("1265913",  "twWQgEYSoiKU", "http://local.mingyuanriji.cn/web/index.php?r=job-debug/index");
        $auth->auth();

        if($auth->error){
            echo $auth->error;
        }else{
            $auth->tokenInfo();
            exit;
        }*/
    }

}
