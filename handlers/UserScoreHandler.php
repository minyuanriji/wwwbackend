<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-15
 * Time: 10:42
 */

namespace app\handlers;


class UserScoreHandler extends BaseHandler
{
    const SCORE_REDUCE = 'score_reduce'; // 积分减少
    const SCORE_REFUND = 'score_refund'; // 积分返回

    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::warning("---RelationHandler start--");

        \Yii::$app->on(UserScoreHandler::SCORE_REDUCE, function ($event) {

        });

        \Yii::$app->on(UserScoreHandler::SCORE_REFUND, function ($event) {

            \Yii::warning('积分退还事件捕捉');


        });
    }
}
