<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 通用基础类
 * Author: zal
 * Date: 2020-04-15
 * Time: 17:45
 */

namespace app\core\currency;


interface BaseCurrency
{
    // 收入
    public function add($price, $desc, $customDesc);

    // 支出
    public function sub($price, $desc, $customDesc);

    // 查询
    public function select();

    // 退款
    public function refund($price, $desc, $customDesc);
}
