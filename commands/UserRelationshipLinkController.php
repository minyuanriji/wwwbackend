<?php
namespace app\commands;

use app\core\ApiCode;
use app\forms\common\UserRelationshipLinkForm;
use app\models\User;
use app\models\UserRelationshipLink;
use yii\console\Controller;
use yii\log\Logger;

class UserRelationshipLinkController extends Controller {

    const JOB_HEAT_TIMEOUT      = 60;
    const JOB_CAHCE_DURATION    = 3600 * 24 * 7;
    const JOB_UNIQUE_CACHE_KEY  = "UserRelationshipLinkUniqueCacheKey";

    /**
     * 重建用户关系表
     */
    public function actionRebuildJob(){
        while (true){

            if(!$this->hasJob())
                continue;

            /** 开始执行重建任务 */
            $cache = \Yii::$app->getCache();
            $cacheData = $cache->get(UserRelationshipLinkForm::REBUILD_JOB_CACHE_KEY);
            $cacheData['status'] = UserRelationshipLinkForm::REBUILD_JOB_STATUS_RUNNING;
            $cache->set(UserRelationshipLinkForm::REBUILD_JOB_CACHE_KEY, $cacheData, self::JOB_CAHCE_DURATION);

            //清空所有关系链
            UserRelationshipLink::deleteAll();

            //表前缀
            $tablePrefix = \Yii::$app->db->tablePrefix;

            //锁住待操作表
            \Yii::$app->db->pdo->exec("LOCK TABLES {$tablePrefix}user AS u READ,{$tablePrefix}user READ,{$tablePrefix}user_relationship_link WRITE,{$tablePrefix}user_relationship_link AS url WRITE");

            try {
                while (true){

                    //获取一个待操作用户
                    $query = User::find()->alias("u");
                    $query->leftJoin("{{%user_relationship_link}} url", "url.user_id=u.id");
                    $query->andWhere("url.user_id IS NULL");
                    $query->orderBy("u.id ASC");
                    $user = $query->one();

                    if(!$user) break;

                    echo "running rebuild job:" . $user->id . "\n";

                    //设置运行时缓存
                    $cacheData['user_id'] = $user->id;       //当前操作用户ID
                    $cacheData['count']   = $query->count(); //剩余待才操作用户数
                    $this->setRuntimeCache($cacheData);

                    $parent = User::findOne($user->parent_id);
                    $userList = [$user];
                    while($parent){
                        array_unshift($userList, $parent);
                        if($parent->id == $parent->parent_id){
                            $parent->parent_id = 0;
                        }
                        $parent = User::findOne($parent->parent_id);
                    }

                    try {
                        foreach($userList as $key => $user){

                            $link = UserRelationshipLink::findOne(["user_id" => $user->id]);
                            if($link) continue;

                            if($key > 0){
                                $parent = $userList[$key - 1];
                            }else{
                                $user->parent_id = ($user->id != $user->parent_id) ? $user->parent_id : 0;
                                $parent = User::findOne($user->parent_id);
                            }

                            if($parent){
                                $link = UserRelationshipLink::findOne(["user_id" => $parent->id]);
                                if(!$link){
                                    throw new \Exception("关系链异常");
                                }

                                $left = $link->right;
                                $right = $left + 1;

                                UserRelationshipLink::updateAllCounters(["left" => 2, "right" => 2],
                                    "`left` > '".$link->right."'");
                                UserRelationshipLink::updateAllCounters(["right" => 2],
                                    "`left` <= '".$link->left."' AND `right` >= '".$link->right."'");

                            }else{
                                $maxLink = UserRelationshipLink::find()->select(["right"])->orderBy("`right` DESC")->limit(1)->one();
                                $left = $maxLink ? ($maxLink->right + 1) : 0;
                                $right = $left + 1;
                            }

                            $link = new UserRelationshipLink([
                                "user_id"   => $user->id,
                                "parent_id" => $user->parent_id,
                                "left"      => $left,
                                "right"     => $right
                            ]);
                            if(!$link->save()){
                                throw new \Exception(json_encode($link->getErrors()));
                            }
                        }
                    }catch (\Exception $e){
                        throw $e;
                    }
                }
            }catch (\Exception $e){
                $cacheData['error'] = $e->getMessage();
                //\Yii::$app->log->getLogger()->log($e->getMessage(), Logger::LEVEL_ERROR, "UserRelationshipLinkRebuildJob");
            }

            \Yii::$app->db->createCommand("UNLOCK tables")->execute();

            //结束
            $cacheData['status'] = UserRelationshipLinkForm::REBUILD_JOB_STATUS_FINISHED;
            $cache->set(UserRelationshipLinkForm::REBUILD_JOB_CACHE_KEY, $cacheData, self::JOB_CAHCE_DURATION);
        }

    }

    /**
     * 设置运行时缓存
     */
    private function setRuntimeCache($data = []){
        $cache = \Yii::$app->getCache();
        $cacheData = $cache->get(UserRelationshipLinkForm::REBUILD_JOB_CACHE_KEY);
        if(!empty($cacheData) && $cacheData['status'] != UserRelationshipLinkForm::REBUILD_JOB_STATUS_RUNNING){ //运行状态异常
            exit("runtime status error");
        }

        $cacheData = array_merge($data, [
            'heat_time' => time(),
            'status'    => UserRelationshipLinkForm::REBUILD_JOB_STATUS_RUNNING
        ]);

        $cache->set(UserRelationshipLinkForm::REBUILD_JOB_CACHE_KEY, $cacheData, self::JOB_CAHCE_DURATION );

    }

    /**
     * 判断是否有任务要执行
     * @return bool
     */
    private function hasJob(){
        $cache = \Yii::$app->getCache();
        $cacheData = $cache->get(UserRelationshipLinkForm::REBUILD_JOB_CACHE_KEY);
        if(empty($cacheData) || $cacheData['status'] != UserRelationshipLinkForm::REBUILD_JOB_STATUS_WAITTING){
            if($cacheData && $cacheData['status'] == UserRelationshipLinkForm::REBUILD_JOB_STATUS_RUNNING){ //还在运行中
                if(!empty($cacheData['heat_time']) ){
                    if((time() - $cacheData['heat_time']) > self::JOB_HEAT_TIMEOUT){ //超时处理
                        $cacheData['error']  = 'rebuild job timeout';
                        $cacheData['status'] = UserRelationshipLinkForm::REBUILD_JOB_STATUS_FINISHED;
                        $cache->set(UserRelationshipLinkForm::REBUILD_JOB_CACHE_KEY, $cacheData, self::JOB_CAHCE_DURATION);
                    }else{
                        exit("任务重复！");
                    }
                }else{
                    $cache->delete(UserRelationshipLinkForm::REBUILD_JOB_CACHE_KEY);
                }
            }
            return false;
        }
        return true;
    }
}