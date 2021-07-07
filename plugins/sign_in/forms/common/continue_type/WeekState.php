<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件-处理周签到公共类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:10
 */

namespace app\plugins\sign_in\forms\common\continue_type;

use app\plugins\sign_in\jobs\ClearContinueJob;

class WeekState extends BaseState
{
    public function setJob()
    {
        $nextMonday = strtotime('next monday');
        $delay = $nextMonday - time();
        \Yii::$app->queue->delay($delay)->push(new ClearContinueJob([
            'mall' => $this->common->mall
        ]));
    }

    public function clearContinue()
    {
        $week = date('N');
        $count = 0;
        if ($week == 1) {
            $count = $this->common->clearContinue();
        }
        return $count;
    }
}
