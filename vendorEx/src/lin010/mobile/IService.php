<?php

namespace lin010\mobile;

interface IService
{
    /**
     * 获取手机号归属地信息
     * @params string $mobile
     * @return QueryMobileInfoResult
     */
    public function queryMobileInfo($mobile);
}