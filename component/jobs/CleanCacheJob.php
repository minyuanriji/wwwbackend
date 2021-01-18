<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-10
 * Time: 19:16
 */

namespace app\component\jobs;


use yii\queue\JobInterface;
use yii\queue\Queue;

class CleanCacheJob implements JobInterface
{

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        $path = \Yii::$app->runtimePath . '/wechat-cache';
        if (file_exists($path)) {
            remove_dir($path);
        }
    }
}
