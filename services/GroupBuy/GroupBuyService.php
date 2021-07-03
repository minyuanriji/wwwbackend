<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/9/23
 * Time: 14:57
 */

namespace app\services\GroupBuy;

class GroupBuyService
{
    function unsetOrderMenu($menuList)
    {
        if (!empty($menuList)) {
            foreach ($menuList as &$value) {
                if ($value['sign'] == "group_buy") {
                    unset($value);
                }
            }
        }

        return $menuList;
    }
}