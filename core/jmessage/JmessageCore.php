<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 极光聊天核心类
 * Author: zal
 * Date: 2020-04-24
 * Time: 09:11
 */

namespace app\core\jmessage;

use app\logic\OptionLogic;
use app\models\Option;
use JMessage\JMessage;
use yii\base\Component;

class JmessageCore extends Component
{
    public $appKey;
    public $masterSecret;

    public $jmModel = "jmessage";

    public function init()
    {
        parent::init();
        $this->jmModel = new JMessage($this->appKey, $this->masterSecret,[ 'disable_ssl' => true ]);
    }
}
