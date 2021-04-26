<?php
namespace app\forms\common;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\models\UserRelationshipLink;

class UserRelationshipLinkForm extends BaseModel{

    public $id;

    const REBUILD_JOB_CACHE_KEY       = "UserRelationshipLinkRebuild";
    const REBUILD_JOB_STATUS_WAITTING = 0;
    const REBUILD_JOB_STATUS_RUNNING  = 1;
    const REBUILD_JOB_STATUS_FINISHED = 2;

    public function rules(){
        return [
            [['id'], 'required'],
            [['id'], 'integer']
        ];
    }

    /**
     * 重建用户关系链
     * @return array
     */
    public static function rebuild(){

        try {
            $cache = \Yii::$app->getCache();

            $cacheData = $cache->get(self::REBUILD_JOB_CACHE_KEY);

            if(!empty($cacheData) && $cacheData['status'] != self::REBUILD_JOB_STATUS_FINISHED){
                throw new \Exception("上一个重建队列还未完成，请勿重复操作！");
            }

            $cacheData['start']  = time();
            $cacheData['error']  = "";
            $cacheData['status'] = self::REBUILD_JOB_STATUS_WAITTING;

            $cache->set(self::REBUILD_JOB_CACHE_KEY, $cacheData, 60);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功',
                'data' => $cacheData
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 统计用户直推
     * @param User $user
     * @return int
     */
    public static function countDirectPush(User $user){
        return (int)User::find()->where([
            "parent_id" => $user->id
        ])->count();
    }

    /**
     * 统计用户团队数
     * @param User $user
     * @return int
     */
    public static function countUserTeam(User $user){
        $userLink = UserRelationshipLink::findOne(["user_id" => $user->id]);
        $count = (int)UserRelationshipLink::find()->andWhere([
            "AND",
            "`left`>'".$userLink->left."'",
            "`right`<'".$userLink->right."'"
        ])->count();
    }
}