<?php
namespace app\component\efps\lib\wechat;

use app\component\efps\lib\InterfaceEfps;
use app\component\efps\lib\ParamsBuilder;

class BindAppId extends ParamsBuilder implements InterfaceEfps{

    public function getApi(){
        return "/api/cust/wechat/wechatConfig/subAppid";
    }

    public function build($params){

        $this->params = array_merge($params, [

        ]);

        return $this;
    }

}