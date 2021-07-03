<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 自定义商品显示价格模型
 * Author: xuyaoxiang
 * Date: 2020/10/28
 * Time: 10:06
 */

namespace app\services\Goods;

use app\services\ModelServices;

class GoodsPriceDisplayServices extends ModelServices
{
    public function __construct()
    {
        parent::__construct('app\models\GoodsPriceDisplay');
    }
}