<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 模板消息发送者抽象类
 * Author: zal
 * Date: 2020-04-14
 * Time: 14:50
 */

namespace app\forms\common\template;

use app\models\BaseModel;

abstract class TemplateSender extends BaseModel
{
    public $is_need_form_id = true;
    abstract public function sendTemplate($arg = array());
}
