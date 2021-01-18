<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 卡券提醒任务类
 * Author: zal
 * Date: 2020-04-21
 * Time: 18:16
 */

namespace app\component\jobs;

use app\forms\common\template\tplmsg\AccountChange;
use app\models\User;
use app\models\UserCard;
use yii\base\Component;
use yii\queue\JobInterface;

class UserCardCreatedJob extends Component implements JobInterface
{
    public $id;

    public $mall;
    public $user_id;

    public function execute($queue)
    {
        try {
            $model = UserCard::find()->with('card')->where([
                'mall_id' => $this->mall->id,
                'id' => $this->id,
                'is_use' => 0,
                'is_delete' => 0,
            ])->andWhere(['>', 'end_at', mysql_timestamp()])->one();

            if (!$model) {
                throw new \Exception('卡卷已过期或已使用或已删除');
            }
            try {
                $tplMsg = new AccountChange([
                    'page' => 'pages/card/index/index',
                    'user' => User::findOne($this->user_id),
                    'remark' => '[' . $model->name . ']',
                    'desc' => '卡卷即将过期，请尽快使用'
                ]);
                $tplMsg->send();
            } catch (\Exception $exception) {
                \Yii::warning('卡卷过期提醒发送失败 => ' . $exception->getMessage());
            }
            return $model;
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
        }
    }
}
