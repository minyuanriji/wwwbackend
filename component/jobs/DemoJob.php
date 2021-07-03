<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-21
 * Time: 9:12
 */

namespace app\component\jobs;


use yii\base\BaseObject;
use yii\queue\closure\Job;
use yii\queue\JobInterface;
use yii\queue\Queue;

class DemoJob extends BaseObject implements JobInterface
{


    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        // TODO: Implement execute() method.
          \Yii::warning('---------------------------------------');

         echo '当前执行测试队列';
    }
}