<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-21
 * Time: 20:35
 */

namespace app\plugins\agent\jobs;



use app\models\User;
use app\models\UserChildren;
use app\models\UserGrowth;
use app\models\UserParent;
use app\plugins\agent\models\Agent;
use yii\base\Component;
use yii\queue\JobInterface;
use yii\queue\Queue;

class AgentInsertJob extends Component implements JobInterface
{
    public $user_id;
    public $mall_id;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        // TODO: Implement execute() method.
        $list = UserParent::find()->where(['user_id' => $this->user_id, 'is_delete' => 0, 'mall_id' => $this->mall_id])->all();
        foreach ($list as $item) {
            /**
             * @var UserParent $item
             *
             */
            // 所有的分销商
            $growth = UserGrowth::findOne(['user_id' => $item->parent_id, 'keyword' => UserGrowth::KEY_DISTRIBUTION_COUNT, 'is_delete' => 0, 'mall_id' => $this->mall_id]);
            if (!$growth) {
                $growth = new UserGrowth();
                $growth->user_id = $item->parent_id;
                $growth->keyword = UserGrowth::KEY_DISTRIBUTION_COUNT;
                $growth->mall_id = $this->mall_id;
            }
            $count = Agent::find()
                ->alias('d')
                ->leftJoin(['u' => User::tableName()], 'u.id=d.user_id')
                ->leftJoin(['uc' => UserChildren::tableName()], 'uc.child_id=u.id')
                ->andWhere(['uc.user_id' => $item->parent_id, 'd.is_delete' => 0, 'uc.is_delete' => 0])
                ->count();
            $growth->value = $count;
            $growth->save();

            // 所有一级
            $growth = UserGrowth::findOne(['user_id' => $item->parent_id, 'keyword' => UserGrowth::KEY_DISTRIBUTION_FIRST_COUNT, 'is_delete' => 0, 'mall_id' => $this->mall_id]);
            if (!$growth) {
                $growth = new UserGrowth();
                $growth->user_id = $item->parent_id;
                $growth->keyword = UserGrowth::KEY_DISTRIBUTION_FIRST_COUNT;
                $growth->mall_id = $this->mall_id;
            }
            $count = Agent::find()
                ->alias('d')
                ->leftJoin(['u' => User::tableName()], 'u.id=d.user_id')
                ->leftJoin(['uc' => UserChildren::tableName()], 'uc.child_id=u.id')
                ->andWhere(['uc.user_id' => $item->parent_id, 'd.is_delete' => 0, 'uc.is_delete' => 0, 'uc.level' => 1])
                ->count();
            $growth->value = $count;
            $growth->save();
        }
    }
}