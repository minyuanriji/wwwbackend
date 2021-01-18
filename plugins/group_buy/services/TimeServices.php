<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 时间公用方法
 * Author: xuyaoxiang
 * Date: 2020/9/8
 * Time: 14:41
 */

namespace app\plugins\group_buy\services;

class TimeServices
{
    const UNIX_ONE_DAY=86400;
    const UNIX_ONE_HOUR=3600;
    /**
     * 剩余时间,返回时间戳
     * @param $end_time
     * @return false|int
     */
    public function getRemainingTimeUnix($end_time)
    {
        $unix_end_time  = strtotime($end_time);
        $now            = time();
        $remaining_time = $unix_end_time - $now;
        $remaining_time = $remaining_time > 0 ? $remaining_time : 0;

        return $remaining_time;
    }

    /**
     * 剩余时间,返回时分秒
     * @param $end_time
     * @return false|string
     */
    public function getReaminingTime($end_time)
    {
        $unix = $this->getRemainingTimeUnix($end_time);
        return $this->time2string($unix);
    }

    /**
     * 剩余时间(和当前时间对比) 2020-01-01 15:15:03
     * @param $end_time
     * @return string 23小时58分钟
     */
    public function getReaminingTimeMin($end_time)
    {
        $unix = $this->getRemainingTimeUnix($end_time);

        return $this->time2stringNoSeconds($unix);
    }

    /**
     * 剩余时间(和当前时间对比) 2020-01-01 15:15:03
     * @param $end_time
     * @return string 1天1小时58分钟 or 23小时58分钟
     */
    public function getReaminingTimeStartAt($end_time)
    {
        $unix = $this->getRemainingTimeUnix($end_time);

        //是否大于一天
        if ($unix > self::UNIX_ONE_DAY) {
            return $this->time2stringDay($unix);
        }
        //小于一天，大于一小时
        if($unix > self::UNIX_ONE_HOUR){
            return $this->time2stringNoSeconds($unix);
        }
        //小于一小时;

        return $this->time2stringLessOneHour($unix);
    }

    /**
     * @param $second
     * @return string 23小时58分钟58秒
     */
    function time2string($second)
    {
        $hour   = floor($second / 3600);
        $second = $second % 3600;
        $minute = floor($second / 60);
        $second = $second % 60;
        // 不用管怎么实现的，能用就ok
        return $hour . '小时' . $minute . '分' . $second . '秒';
    }

    /**
     * @param $second
     * @return string 1小时58分钟
     */
    function time2stringNoSeconds($second)
    {
        $hour   = floor($second / 3600);
        $second = $second % 3600;
        $minute = floor($second / 60);
        $second = $second % 60;
        // 不用管怎么实现的，能用就ok
        return $hour . '小时' . $minute . '分';
    }

    /**
     * @param $second
     * @return string 58分钟;
     */
    function time2stringLessOneHour($second)
    {
        $second = $second % 3600;
        $minute = floor($second / 60);
        // 不用管怎么实现的，能用就ok
        return $minute . '分';
    }

    /**
     * @param $second
     * @return string 1天23小时06分
     */
    function time2stringDay($second){
        $day = floor($second/(3600*24));
        $second = $second%(3600*24);
        $hour = floor($second/3600);
        $second = $second%3600;
        $minute = floor($second/60);
        // 不用管怎么实现的，能用就ok
        return $day.'天'.$hour.'小时'.$minute.'分';
    }

    public function getMinutes($start_at)
    {
        $unix = strtotime($start_at);

        $beginningMinutes = ($unix - time()) / 60;

        return round($beginningMinutes);
    }

    public function getSeconds($start_at)
    {
        $unix = strtotime($start_at);

        $beginningMinutes = ($unix - time());

        return round($beginningMinutes);
    }

    /**
     * 时间戳转时分秒 08:02:01
     * @param $unxi_time
     * @return false|string
     */
    public function unixTimeToHour($unxi_time)
    {
        return date("H:i:s", $unxi_time);
    }

    public function unixTimeToMin($unxi_time)
    {
        return date("H:i:s", $unxi_time);
    }

    /**
     * 获取结束时间
     * @param $start_at
     * @param $vaild_time
     * @return false|string
     */
    public function getUnixEndTime($vaild_time)
    {
        $vaild_time_unix = $vaild_time * 60;
        $end_at          = time() + $vaild_time_unix;

        return $end_at;
    }

    public function vaildTimeFormat($vaild_time)
    {

        $unix = intval($vaild_time * 60);

        return $this->time2stringNoSeconds($unix);
    }
}