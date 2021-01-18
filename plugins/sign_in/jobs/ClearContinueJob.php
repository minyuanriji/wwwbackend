<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件-清除连续签到任务类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:40
 */

namespace app\plugins\sign_in\jobs;

use app\models\Mall;
use app\plugins\sign_in\forms\common\Common;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class ClearContinueJob extends BaseObject implements JobInterface
{
    public $mall;

    public function execute($queue)
    {
        try {
            $this->mall = Mall::findOne($this->mall->id);
            \Yii::$app->setMall($this->mall);
            $common = Common::getCommon($this->mall);
            $config = $common->getConfig();
            $continueTypeClass = $common->getContinueTypeClass($config->continue_type);
            $count = $continueTypeClass->clearContinue();
            $continueTypeClass->setJob();
        } catch (\Exception $exception) {
            \Yii::error($exception->getMessage());
        }
    }
}
