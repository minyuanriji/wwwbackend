<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件-处理当月签到公共类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:10
 */

namespace app\plugins\sign_in\forms\common\continue_type;


use app\plugins\sign_in\jobs\ClearContinueJob;

class MonthState extends Base
{
    public function setJob()
    {
        $nowDate = date('Y-m-01', strtotime(date("Y-m-d")));
        $nextDate = strtotime("$nowDate + 1 month");
        $delay = $nextDate - time();
        \Yii::$app->queue->delay($delay)->push(new ClearContinueJob([
            'mall' => $this->common->mall
        ]));
    }

    public function clearContinue()
    {
        $day = date('j');
        $count = 0;
        if ($day == 1) {
            $count = $this->common->clearContinue();
        }
        return $count;
    }
}
