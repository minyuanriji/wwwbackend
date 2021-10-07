<?php

namespace lin010\alibaba\c2b2b\api;

class GetGoodsListForUserChoosed extends BaseAPI{

    /**
     * 返回API参数
     * @return array
     */
    public static function paramKeys(){
        return ["pageNo", "pageSize", "groupId", "title"];
    }

    /**
     * 是否需要授权
     * @return boolean
     */
    public function needAuth(){
        return true;
    }

    /**
     * 获取路径
     * @return string
     */
    public function getPath(){
        return "com.alibaba.p4p/alibaba.cps.op.listCybUserGroupFeed";
    }

    /**
     * 获取路径
     * @return string
     */
    public function getResponse(){
        return new GetGoodsListForUserChoosedResponse();
    }
}