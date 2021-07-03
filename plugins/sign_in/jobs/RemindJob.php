<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件-签到提醒任务类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:40
 */

namespace app\plugins\sign_in\jobs;

use app\models\Mall;
use app\plugins\sign_in\forms\common\Common;
use app\plugins\sign_in\models\SignInConfig;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * @property Mall $mall
 * @property SignInConfig $config
 */
class RemindJob extends BaseObject implements JobInterface
{
    public $mall;

    public function execute($queue)
    {
        $this->mall = Mall::findOne($this->mall->id);
        \Yii::$app->setMall($this->mall);
        $common = Common::getCommon($this->mall);
        $t = \Yii::$app->db->beginTransaction();
        try {
            $config = $common->getConfig();
            if (!$config) {
                throw new \Exception('签到未开放');
            }
            if ($config->status == 0) {
                throw new \Exception('签到未开启');
            }
            if ($config->is_remind == 0) {
                throw new \Exception('签到未开启提醒功能');
            }
            $time = time();
            $configTime = strtotime($config->time);
            // 提醒时间没有到，重新添加定时任务
            if ($configTime - $time > 0) {
                return ;
            }

            $signInUserAll = $common->getSignInUserByRemind();

            foreach ($signInUserAll as $signInUser) {
                try {
                    $template = $common->getCommonTemplate($signInUser->user);
                    $template->send();
                    $common->addSignInUserRemind([
                        'user_id' => $signInUser->user_id,
                        'mall_id' => $signInUser->mall_id,
                        'is_delete' => 0,
                        'date' => date('Y-m-d H:i:s'),
                        'is_remind' => 1,
                    ]);
                } catch (\Exception $exception) {
                    continue;
                }
            }
            $common->addRemindJob();
            $t->commit();
        } catch (\Exception $exception) {
            $common->addRemindJob();
            $t->rollBack();
        }
    }
}
