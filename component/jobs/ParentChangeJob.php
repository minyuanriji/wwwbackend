<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-21
 * Time: 16:11
 */

namespace app\component\jobs;

use app\logic\RelationLogic;
use app\models\Mall;
use app\models\User;
use app\models\UserChildren;
use app\models\UserParent;
use yii\base\BaseObject;
use yii\queue\JobInterface;


class ParentChangeJob extends BaseObject implements JobInterface
{

    public $user_id;
    public $parent_id; // 变更后的上级id
    public $mall_id;

    public function execute($queue)
    {
        return;
        \Yii::$app->setMall(Mall::findOne($this->mall_id));
        $user_id = $this->user_id;
        $parent_id = $this->parent_id;
        $user = User::findOne($user_id);
        \Yii::warning("ParentChangeJob execute user_id=".$user_id.";parent_id".$this->parent_id);
        if ($user) {
            \Yii::warning("ParentChangeJob execute1 user=".var_export($user,true));
            if ($user->parent && $user->parent->is_inviter) {
                \Yii::warning("ParentChangeJob execute1 user_id=".$user_id);
                //新增子级
                $children = UserChildren::findOne(['level' => 1, 'child_id' => $user_id, 'mall_id' => $this->mall_id]);
                \Yii::warning("ParentChangeJob execute2 children=".var_export($children,true));
                if ($children) {
                    if ($children->user_id != $parent_id) {
                        UserChildren::updateAll(['is_delete' => 1], ['child_id' => $user_id, 'is_delete' => 0, 'mall_id' => $this->mall_id]);
                        $children = false;
                    }
                }
                if (!$children) {
                    $children = new UserChildren();
                    $children->mall_id = $this->mall_id;
                    $children->user_id = $parent_id;
                    $children->level = 1;
                    $children->child_id = $user_id;
                    $children->save();
                }
                $userParent = UserParent::findOne(['user_id' => $user_id, 'level' => 1, 'mall_id' => $this->mall_id, 'is_delete' => 0]);
                if ($userParent) {
                    if ($userParent->parent_id != $parent_id) {
                        UserParent::updateAll(['is_delete' => 1], ['user_id' => $user_id, 'is_delete' => 0, 'mall_id' => $this->mall_id]);
                        $userParent = false;
                    }
                }
                if (!$userParent) {
                    $userParent = new UserParent();
                    $userParent->mall_id = $this->mall_id;
                    $userParent->user_id = $user_id;
                    $userParent->level = 1;
                    $userParent->parent_id = $parent_id;
                    $userParent->save();
                }
                $parent_list = UserChildren::find()->where(['child_id' => $this->parent_id, 'mall_id' => $this->mall_id, 'is_delete' => 0])->orderBy('level asc')->all();
                \Yii::warning("ParentChangeJob execute3 parent_list=".var_export($parent_list,true));
                /**
                 * @var UserChildren $parent_list []
                 * @var UserChildren $parent
                 */
                foreach ($parent_list as $i => $parent) {
                    $children = UserChildren::find()->where(['user_id' => $parent->user_id, 'child_id' => $user_id, 'mall_id' => $this->mall_id, 'is_delete' => 0])->one();
                    if (!$children) {
                        $children = new UserChildren();
                        $children->mall_id = $this->mall_id;
                        $children->user_id = $parent->user_id;
                        $children->child_id = $user_id;
                    }
                    $children->level = $i + 2;  //因为上一级的已经加了 所以层级应该从2开始
                    $children->save();
                    $userParent = UserParent::find()->where(['user_id' => $user_id, 'parent_id' => $parent->user_id, 'mall_id' => $this->mall_id, 'is_delete' => 0])->one();
                    if (!$userParent) {
                        $userParent = new UserParent();
                        $userParent->mall_id = $this->mall_id;
                        $userParent->user_id = $user_id;
                        $userParent->parent_id = $parent->user_id;
                    }
                    $userParent->level = $i + 2;
                    $userParent->save();
                }
            }
            RelationLogic::changeUserThreeParentId($user_id);
        }
    }
}
