<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-08
 * Time: 19:16
 */

namespace app\component\jobs;

use app\models\Admin;
use app\models\Mall;
use yii\base\Component;
use yii\queue\JobInterface;

class UserUpdateJob extends Component implements JobInterface
{
    public $user_id;

    public function execute($queue)
    {
        try {
            \Yii::warning('账号更新Job开始执行');
            /** @var Admin $admin */
            $admin = Admin::find()->where(['id' => $this->user_id, 'is_delete' => 0])->one();
            if (!$admin) {
                throw new \Exception('管理员账号不存在');
            }

            if ($admin->expired_at == '0') {
                throw new \Exception('账号有效期为永久');
            }

            $expiredAt = $admin->expired_at - time();
            if ($expiredAt < 0) {
                $res = Mall::updateAll([
                    'expired_at' => time()
                ], [
                    'admin_id' => $admin->id,
                    'is_delete' => 0
                ]);
            }

            \Yii::warning('账号更新Job执行完成');

        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
        }
    }
}
