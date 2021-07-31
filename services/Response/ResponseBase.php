<?php
/**
 * Created by PhpStorm.
 * User: wmc
 * Date: 21/7/31
 * Time: 上午11:20
 */

namespace App\Services\Response;

abstract class ResponseBase
{
    const CODE = [
        -1  => '操作未授权',
        0   => '操作成功',
        1   => '操作失败',
    ];
}