<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

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
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex($message = 'hello world')
    {
        \Yii::$app->on(Order::EVENT_CREATED, function ($event) {
            sleep(30);
            \Yii::warning('来到订单创建事件');
        });
        exit;
        $dateFolder = "20200919";
        $baseWebPath = \Yii::$app->basePath . '/web';
        $baseWebUrl = "http://jx.com/web";
        $saveThumbFolder = '/uploads/video/original/' . $dateFolder . '/';
        $saveName = "11.mp4";
        $path = $baseWebPath . $saveThumbFolder . $saveName;
        $rand = 1;
        $saveViedoImg = $baseWebPath.$saveThumbFolder.$rand.'frame.jpg';
        VideoLogic::getVideoImage($path,$saveViedoImg,$rand);

        $videoDuration = VideoLogic::getVideoDuration($path);
        echo "time:".$videoDuration."\n";
        echo "time:".intval($videoDuration)."\n";
        echo $saveViedoImg."\n";
        $thumb_url = $baseWebUrl . $saveThumbFolder.$rand.'frame.jpg';
        echo $thumb_url;
        exit;
        echo ROOT_PATH;
        echo str_replace(ROOT_PATH,"web","");exit;
        $str = "/web/temp/854e4e2ceac92c6f40f2db002ca638aa751b737b1.jpg";
        echo substr(dirname($str),1);exit;
        echo $message . "\n";

        return ExitCode::OK;
    }

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
