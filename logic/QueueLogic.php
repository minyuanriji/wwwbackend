<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 队列相关逻辑处理
 * Author: zal
 * Date: 2020-09-26
 * Time: 14:36
 */

namespace app\logic;

class QueueLogic
{
    public static function loadQueueDrive($driveName){
        try{
            $queue = \Yii::$app->$driveName;
        }catch (\Exception $ex){
            print_r($ex->getMessage());
            exit;
            $queue = \Yii::$app->queue;
        }
        return $queue;
    }
}