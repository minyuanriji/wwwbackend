<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 后台首页
 * Author: zal
 * Date: 2020-04-08
 * Time: 16:12
 */

namespace app\controllers\admin;

use app\core\ApiCode;
use app\forms\admin\AdminForm;
use app\forms\admin\AdminEditForm;
use app\forms\common\AttachmentForm;
use app\helpers\SerializeHelper;
use app\logic\AuthLogic;
use app\models\Admin;

use app\events\OrderEvent;
use app\forms\common\order\OrderCommon;
use app\logic\VideoLogic;
use app\models\Order;
use app\models\User;
use app\models\UserChildren;
use app\models\UserParent;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use yii\web\Controller;

class TestController extends Controller
{
    public function actionUpdateRelation(){
        $userParentList = User::find()->where(["parent_id" => 0,"is_delete" => 0])->all();
        $level = 0;
        foreach ($userParentList as $value){
            $parentFirstId = $value["id"];
            $this->updateRelation($parentFirstId,$level);
        }
        echo "ok";
    }

    public function updateRelation($parentId,$level){
        $mall_id = 5;
        $userParentList = User::find()->where(["parent_id" => $parentId,"is_delete" => 0])->all();
        $level = $level + 1;
        foreach ($userParentList as $value){
            $level = $level == 0 ? 1 : $level;
            $userId = $value["id"];
            $children = UserChildren::findOne(['level' => $level,'user_id' => $parentId, 'child_id' => $userId, 'mall_id' => $mall_id]);
            if(empty($children)){
                $children = new UserChildren();
                $children->mall_id = $mall_id;
                $children->user_id = $parentId;
                $children->level = $level;
                $children->child_id = $userId;
                $children->save();
            }

            $userParent = UserParent::findOne(['user_id' => $userId,'parent_id'=>$parentId, 'level' => $level, 'mall_id' => $mall_id, 'is_delete' => 0]);
            if(empty($userParent)){
                $userParent = new UserParent();
                $userParent->mall_id = $mall_id;
                $userParent->user_id = $userId;
                $userParent->level = $level;
                $userParent->parent_id = $parentId;
                $userParent->save();
            }

            $parent_list = UserChildren::find()->where(['child_id' => $parentId, 'mall_id' => $mall_id, 'is_delete' => 0])->orderBy('level asc')->all();
            \Yii::warning("ParentChangeJob execute3 parent_list=".var_export($parent_list,true));
            /**
             * @var UserChildren $parent_list []
             * @var UserChildren $parent
             */
            if(!empty($parent_list)){
                foreach ($parent_list as $i => $parent) {
                    $children = UserChildren::find()->where(['user_id' => $parent->user_id, 'child_id' => $userId, 'mall_id' => $mall_id, 'is_delete' => 0])->one();
                    if (!$children) {
                        $children = new UserChildren();
                        $children->mall_id = $mall_id;
                        $children->user_id = $parent->user_id;
                        $children->child_id = $userId;
                    }
                    $children->level = $i + 2;  //因为上一级的已经加了 所以层级应该从2开始
                    $children->save();
                    $userParent = UserParent::find()->where(['user_id' => $userId, 'parent_id' => $parent->user_id, 'mall_id' => $mall_id, 'is_delete' => 0])->one();
                    if (!$userParent) {
                        $userParent = new UserParent();
                        $userParent->mall_id = $mall_id;
                        $userParent->user_id = $userId;
                        $userParent->parent_id = $parent->user_id;
                    }
                    $userParent->level = $i + 2;
                    $userParent->save();
                }
                $level = 0;
            }else{
                $child_list = UserChildren::find()->where(['child_id' => $userId, 'mall_id' => $mall_id, 'is_delete' => 0])->orderBy('level asc')->all();
                if(!empty($child_list)){
                    $level = 0;
                }
            }
            $this->updateRelation($userId,$level);
        }
    }

}
