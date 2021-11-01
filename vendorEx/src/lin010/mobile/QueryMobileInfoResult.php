<?php

namespace lin010\mobile;

class QueryMobileInfoResult
{
    const CODE_SUCC = 0;
    const CODE_FAIL = -1;

    public $code;
    public $message;

    public $province; //省
    public $city; //市
    public $platName; //电信/移动/联通
    public $platCode; //10000/10086/10010

}