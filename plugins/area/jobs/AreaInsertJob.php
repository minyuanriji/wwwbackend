<?php

namespace app\plugins\area\jobs;

use app\models\User;
use app\models\UserChildren;
use app\models\UserGrowth;
use app\models\UserParent;
use app\plugins\area\models\Area;
use yii\base\Component;
use yii\queue\JobInterface;
use yii\queue\Queue;

class AreaInsertJob extends Component implements JobInterface
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
            // 所有的分销商
            $growth = UserGrowth::findOne(['user_id' => $item->parent_id, 'keyword' => UserGrowth::KEY_DISTRIBUTION_COUNT, 'is_delete' => 0, 'mall_id' => $this->mall_id]);
            if (!$growth) {
                $growth = new UserGrowth();
                $growth->user_id = $item->parent_id;
                $growth->keyword = UserGrowth::KEY_DISTRIBUTION_COUNT;
                $growth->mall_id = $this->mall_id;
            }
            $count = Area::find()
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
            $count = Area::find()
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