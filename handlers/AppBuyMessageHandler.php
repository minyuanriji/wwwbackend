<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-16
 * Time: 14:09
 */

namespace app\handlers;

use app\core\AppMessage;
use app\forms\common\BuyPromptCommon;

class AppBuyMessageHandler extends BaseHandler
{
    /**
     * 事件处理注册
     */
    public function register()
    {
        /** @var AppMessage $appMessage */
        $appMessage = \Yii::$app->appMessage;
        \Yii::$app->on($appMessage::EVENT_APP_MESSAGE_REQUEST, function ($event) {
            \Yii::$app->appMessage->push('buy_data', (new BuyPromptCommon())->get());
        });
    }
}
