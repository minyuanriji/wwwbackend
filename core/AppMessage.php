<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 系统信息
 * Author: zal
 * Date: 2020-04-16
 * Time: 14:22
 */

namespace app\core;

use yii\base\Component;

class AppMessage extends Component
{
    const EVENT_APP_MESSAGE_REQUEST = 'event_app_message_request';
    const EVENT_TEMPLATE_TEST = 'event_template_test'; // 模板消息测试

    private $dataList;

    public function push($key, $data)
    {
        if (!$this->dataList) {
            $this->dataList = [];
        }
        $this->dataList[$key] = $data;
        return true;
    }

    public function getList()
    {
        return $this->dataList ? $this->dataList : null;
    }
}
