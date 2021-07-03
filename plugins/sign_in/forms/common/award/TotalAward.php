<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件-处理累计签到奖励公共类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:10
 */

namespace app\plugins\sign_in\forms\common\award;

class TotalAward extends BaseAward
{
    /**
     * 校验累计签到天数是否达标
     * @return mixed
     * @throws \Exception
     *
     */
    public function check()
    {
        $common = $this->common;
        $signInUser = $common->getSignInUser($this->user);
        if ($signInUser->total < $this->day) {
            throw new \Exception('用户累计签到天数未达到领取条件');
        }
        $sign = $common->getSignInByDay($this->status, $this->day, $this->user);
        if ($sign) {
            throw new \Exception('已领取奖励');
        }
        return true;
    }
}
