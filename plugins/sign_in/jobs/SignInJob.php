<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件-签到任务类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:40
 */

namespace app\plugins\sign_in\jobs;

use app\models\Mall;
use app\models\User;
use app\plugins\sign_in\forms\common\Common;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * @property Mall $mall
 * @property User $user
 */
class SignInJob extends BaseObject implements JobInterface
{
    public $mall;
    public $user;
    public $token;
    public $day;
    public $status;

    public function execute($queue)
    {
        $this->mall = Mall::findOne($this->mall->id);
        $this->user = User::findOne($this->user->id);
        \Yii::$app->setMall($this->mall);
        $common = Common::getCommon($this->mall);
        try {
            $config = $common->getConfig();
            if ($config->status == 0) {
                throw new \Exception('签到未开启');
            }
            $award = $common->getAward($this->status);
            $award->user = $this->user;
            $award->status = $this->status;
            $award->day = $this->day;
            $award->token = $this->token;
            $award->addSignIn();
        } catch (\Exception $exception) {
            $common->addQueueData($this->token, $exception->getMessage());
        }
    }
}
