<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-02
 * Time: 11:22
 */

namespace app\core;

/**
 * Class ApiCode
 * @package app\core
 * 系统状态码调用
 */
class ApiCode
{

    /**
     *  返回成功
     */
    const CODE_SUCCESS = 0;
    /**
     * 返回失败
     */
    const CODE_FAIL = 1;
    /**
     * 黑名单错误码
     */
    const BLACKLIST_CODE_FAIL = 999;
    /**
     * 未登录
     *
     */
    const CODE_NOT_LOGIN = -1;


    /**
     * 商城不存在
     */
    const CODE_MALL_NOT_EXIST = -2;

    /**
     * 状态码：多商户未登录
     */
    const CODE_MCH_NOT_LOGIN = -3;

    /**
     * 状态码：没有绑定手机
     */
    const CODE_BIND_MOBILE = 2;

    /**
     * 名片不存在
     */
    const CODE_CARD_NOT_EXIST = 3;

    /**
     * 名片没有权限
     */
    const CODE_CARD_NOT_AUTH = 4;

    /**
     * 用户没有授权
     */
    const CODE_USER_NOT_AUTH = 5;

        /**
     * 状态码：没有绑定上级
     */
    const CODE_BIND_PARENT = 6;
}