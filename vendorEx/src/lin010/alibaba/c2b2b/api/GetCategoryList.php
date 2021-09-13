<?php

namespace lin010\alibaba\c2b2b\api;

use lin010\alibaba\c2b2b\Response;

class GetCategoryList extends BaseAPI {

    /**
     * 获取结果对象
     * @return Response
     */
    public function getResponse(){
        return new GetCategoryListResponse();
    }

    /**
     * 获取路径
     * @return string
     */
    public function getPath(){
        return "com.alibaba.product/alibaba.category.get";
    }

    /**
     * 是否需要授权
     * @return boolean
     */
    public function needAuth(){
        return false;
    }

    /**
     * 返回API参数
     * @return array
     */
    public static function paramKeys(){
        return ["categoryID"];
    }

}