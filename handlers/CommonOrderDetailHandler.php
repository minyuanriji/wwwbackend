<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-22
 * Time: 9:46
 */

namespace app\handlers;


class CommonOrderDetailHandler extends BaseHandler
{
    const COMMON_ORDER_DETAIL_CREATED = 'common_order_detail_created';
    const COMMON_ORDER_DETAIL_STATUS_CHANGED = 'common_order_detail_status_changed';
    /**
     * 事件处理
     */
    public function register()
    {
    }
}