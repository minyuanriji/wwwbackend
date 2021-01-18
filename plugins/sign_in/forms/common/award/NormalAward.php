<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件-处理正常签到奖励公共类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:10
 */

namespace app\plugins\sign_in\forms\common\award;

class NormalAward extends BaseAward
{
    /**
     * 检测是否正常签到
     * @return bool|mixed
     * @throws \Exception
     */
    public function check()
    {
        $common = $this->common;
        $sign = $common->getSignInByToday($this->user);
        if ($sign) {
            throw new \Exception('今天已签到');
        }
        $this->day = 1;
        return true;
    }

    /**
     * 保存
     * @return bool
     * @throws \Exception
     */
    public function otherSave()
    {
        $common = $this->common;
        $signInUser = $common->getSignInUser($this->user);
        $signInUser->total += 1;

        $yesterday = $common->getSignInByYesterday($this->user);
        if ($yesterday) {
            $signInUser->continue += 1;
        } else {
            $signInUser->continue = 1;
            $signInUser->continue_start = mysql_timestamp();
        }
        if (!$signInUser->save()) {
            throw new \Exception($signInUser);
        }
        return true;
    }
}
