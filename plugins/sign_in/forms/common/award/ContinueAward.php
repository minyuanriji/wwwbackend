<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件-处理连续签到奖励公共类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:10
 */

namespace app\plugins\sign_in\forms\common\award;

class ContinueAward extends BaseAward
{
    /**
     * 校验是否连续签到
     * @return mixed
     * @throws \Exception
     *
     */
    public function check()
    {
        $common = $this->common;
        $signInUser = $common->getSignInUser($this->user);
        if ($signInUser->continue < $this->day) {
            throw new \Exception('用户连续签到天数未达到领取条件');
        }
        $sign = $common->getSignInByContinue($this->status, $this->day, $signInUser);
        if ($sign) {
            throw new \Exception('已领取奖励');
        }
        return true;
    }
}
