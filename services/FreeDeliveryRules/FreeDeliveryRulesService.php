<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 包邮规则
 * Author: xuyaoxiang
 * Date: 2020/10/28
 * Time: 20:20
 */

namespace app\services\FreeDeliveryRules;

use app\services\ModelServices;

class FreeDeliveryRulesService extends ModelServices
{
    public function __construct()
    {
        parent::__construct('app\models\FreeDeliveryRules');
    }
}