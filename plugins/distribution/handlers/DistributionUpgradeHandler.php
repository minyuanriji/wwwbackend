<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-22
 * Time: 14:55
 */

namespace app\plugins\distribution\handlers;


use app\handlers\BaseHandler;
use app\plugins\distribution\jobs\DistributionLevelUpgradeJob;

class DistributionUpgradeHandler extends BaseHandler
{

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-02
     * @Time: 12:57
     * @Note:所有的事件都要通过此方法进去注册
     * @return mixed
     */
    public function register()
    {
        // TODO: Implement register() method.

        \Yii::$app->on(\app\handlers\HandlerRegister::TO_USER_UPGRADE, function ($event) {
            //\Yii::$app->queue->delay(0)->push(new DistributionLevelUpgradeJob(['user_id' => $event->user_id, 'mall_id' => $event->mall_id]));
        });
    }
}