<?php
namespace app\forms\common;


use app\component\jobs\UserRelationshipLinkInsertJob;
use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Order;
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
     * 新增用户关系链
     * @param User $user
     * @return array
     */
    public static function insert($user){
        try {
            \Yii::$app->queue->delay(1)->push(new UserRelationshipLinkInsertJob([
                "user_id"   => $user->id,
                "parent_id" => $user->parent_id
            ]));

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

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
     * 获取直推客户
     * @param User $user
     * @return \app\models\BaseActiveQuery
     */
    public static function getDirectListQuery($user, $userLink){
        if($userLink){
            $left  = $userLink->left;
            $right = $userLink->right;
        }else{
            $left  = 0;
            $right = 0;
        }
        $query = User::find()->alias("u");
        $query->leftJoin("{{%user_relationship_link}} url", "url.user_id=u.id");
        $query->andWhere([
            "AND",
            ["u.parent_id" => $user->id],
            "url.`left` > '{$left}' AND url.`right` < '{$right}'"
        ]);
        return $query;

    }

    /**
     * 获取简推客户
     * @param User $user
     * @param UserRelationshipLink $userLink
     * @return \app\models\BaseActiveQuery
     */
    public static function getSecondList($user, $userLink){
        $query = User::find()->alias("u")->andWhere([
            "AND",
            "u.parent_id <> '".$user->ID."'",
            ["IN", "u.id", static::userTeamQuery($user, $userLink)->select("ut.id")]
        ]);
        return $query;
    }

    /**
     * 统计用户直推
     * @param User $user
     * @return int
     */
    public static function countDirectPush($user, $userLink){
        $query = static::getDirectListQuery($user, $userLink);
        return (int)$query->count();
    }

    /**
     * 统计用户团队数
     * @param User $user
     * @return int
     */
    public static function countUserTeam($user, $userLink){
        if($userLink && !empty($user->role_type)){
            $query = static::userTeamQuery($user, $userLink);
            $query->andWhere("ut.parent_id <> '".$user->id."'");
            $count = (int)$query->count();
        }else{
            $count = 0;
        }
        return $count;
    }

    /**
     * 统计用户团队订单数
     * @param User $user
     * @param UserRelationshipLink $userLink
     * @return int
     */
    public static function countUserTeamOrder($user, $userLink){
        if($userLink && !empty($user->role_type)){
            $query = static::userTeamQuery($user, $userLink);
            $query->innerJoin("{{%order}} o", "o.user_id=ut.id");
            $query->andWhere([
                "AND",
                ["o.is_delete" => 0],
                ["o.is_recycle" => 0],
                ["o.is_pay" => 1]
            ]);
            $count = (int)$query->count();
        }else{
            $count = 0;
        }
        return $count;
    }

    /**
     * 统计用户团队订单金额
     * @param User $user
     * @param UserRelationshipLink $userLink
     * @return int
     */
    public static function countUserTeamOrderTotoal($user, $userLink){
        if($userLink && !empty($user->role_type)){
            $query = static::userTeamQuery($user, $userLink);
            $query->innerJoin("{{%order}} o", "o.user_id=ut.id");
            $query->andWhere([
                "AND",
                ["o.is_delete" => 0],
                ["o.is_recycle" => 0],
                ["o.is_pay" => 1]
            ]);
            $sum = (float)$query->sum("o.total_goods_original_price");
        }else{
            $sum = 0;
        }
        return round($sum,2);
    }

    /**
     * 生成团队查询对象
     * @param User $user
     * @param UserRelationshipLink $userLink
     * @return \app\models\BaseActiveQuery
     */
    public static function userTeamQuery($user, $userLink){
        if($userLink){
            $left  = $userLink->left;
            $right = $userLink->right;
        }else{
            $left  = 0;
            $right = 0;
        }
        $query = UserRelationshipLink::find()->alias("url");
        $query->leftJoin("{{%user}} ut", "ut.id=url.user_id");
        $query->where("(url.`left` > '".$left."' AND url.`right` < '".$right."')");
        return $query;
    }
}