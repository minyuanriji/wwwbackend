<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-02
 * Time: 13:09
 */

namespace app\handlers;


use app\models\RelationSetting;
use yii\base\BaseObject;
use app\handlers\currency\BalanceChangeHandler;
use app\handlers\currency\ScoreChangeHandler;
use app\handlers\currency\IncomeChangeHandler;

/**
 * Class HandlerRegister
 * @package app\handlers
 * 事件注册器
 */
class HandlerRegister extends BaseObject
{
    const TO_USER_UPGRADE = 'to_user_upgrade';


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-02
     * @Time: 13:11
     * @Note:所有的系统事件要注册到这个方法里面，做一个全局注册，在return 里面添加好要注册的事件类即可
     * @return array
     */
    public function getHandlers()
    {
        return [
            OrderCreatedHandler::class,
            OrderCanceledHandler::class,
            OrderPayedHandler::class,
            OrderSentHandler::class,
            OrderConfirmedHandler::class,
            OrderSalesHandler::class,
            OrderRefundConfirmedHandler::class,
            MyHandler::class,
            AppBuyMessageHandler::class,
            GoodsEditHandler::class,
            GoodsDestroyHandler::class,
            OrderChangePriceHandler::class,
            RelationHandler::class,
            CommonOrderFinishedHandler::class,
            UserRelationChangeHandler::class,
            UserInviterStatusChangedHandler::class,
            UserScoreHandler::class,
            MallInitHandler::class,
            TagHandler::class,
            BalanceChangeHandler::class,
            ScoreChangeHandler::class,
            IncomeChangeHandler::class,
            StatisticsHandler::class,
            MemberUpgradeHandler::class,
        ];
    }


}