<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 前台用户逻辑处理类
 * Author: zal
 * Date: 2020-04-09
 * Time: 14:36
 */

namespace app\logic;

use app\events\UserEvent;
use app\helpers\ArrayHelper;
use app\models\BaseModel;
use app\models\CommonOrder;
use app\models\ErrorLog;
use app\models\User;
use app\models\UserChildren;
use app\models\UserInfo;
use app\models\UserParent;
use yii\base\Exception;
use function EasyWeChat\Kernel\Support\get_client_ip;
use app\models\mysql\{UserParent as UserParentModel,UserChildren as UserChildrenModel};

class UserLogic
{

    /**
     * 搜索用户
     * @param $keyword
     * @return array
     */
    public static function searchUser($keyword)
    {
        $keyword = trim($keyword);

        $query = User::find()->alias('u')->select('u.id,u.nickname,u.avatar_url')->where([
            'AND',
            ['or', ['LIKE', 'u.nickname', $keyword], ['u.id' => $keyword], ['u.mobile' => $keyword]],
            ['u.mall_id' => \Yii::$app->mall->id],
        ]);
        $list = $query->orderBy('nickname')->limit(30)->asArray()->all();

        foreach ($list as $k => $v) {
            $list[$k]['avatar'] = $v['avatar_url'];
        }
        return [
            'list' => $list,
        ];
    }

    /**
     * 获取用户数据
     * @param string $columns
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getUserInfo($columns = '*')
    {
        $userInfo = User::find()->where([
            'user_id' => \Yii::$app->user->id,
            'is_delete' => 0
        ])->select($columns)->one();

        return $userInfo;
    }

    public static function getRecommendIds($id){
       $users = User::findOne($id);

    }

    /**
     * 用户注册
     * @param $userData
     * @param User $user 用户信息 绑定手机号情况下，没有userInfo数据时，会调用该方法传该值
     * @return bool
     * @throws \yii\base\Exception
     */
    public static function userRegister($userData,$user = [],$parent_id=0){
        $userData = self::loadUserFields($userData);
        $transaction = \Yii::$app->db->beginTransaction();
        try{
            $appPlatform = isset($userData["platform"]) ? $userData["platform"] : \Yii::$app->appPlatform;
            if(empty($user)){
                //检测用户unionid是否存在，存在获取对应的用户数据
                if(isset($userData["unionid"]) && !empty($userData["unionid"])){
                   $userInfos = self::checkUserUnionidIsExist($userData["unionid"]);
                   if(isset($userInfos->user) && !empty($userInfos->user)){
                      $user = $userInfos->user;
                   }
                }
                if(empty($user)){
                    $user = new User();
                    $user->username = isset($userData["username"]) ? $userData["username"] : "wechat_user";
                    $user->mobile = isset($userData["mobile"]) ? $userData["mobile"] : "";
                    $user->mall_id = \Yii::$app->mall->id;
                    $user->access_token = \Yii::$app->security->generateRandomString();
                    $user->auth_key = \Yii::$app->security->generateRandomString();
                    $user->nickname = isset($userData["nickname"]) ? $userData["nickname"] : "";
                    $user->password = isset($userData["password"]) ? \Yii::$app->getSecurity()->generatePasswordHash($userData["password"]) : "";
                    $user->avatar_url = isset($userData["avatar_url"]) ? $userData["avatar_url"] : "";
                    $user->last_login_at = time();
                    $user->login_ip = get_client_ip();
                    $user->parent_id = isset($userData["parent_id"]) ? $userData["parent_id"] : 0;
                    if(!empty($parent_id)){
                        $user->parent_id = $parent_id;
                    }
                    $user->second_parent_id = isset($userData["second_parent_id"]) ? $userData["second_parent_id"] : 0;
                    $user->third_parent_id = isset($userData["third_parent_id"]) ? $userData["third_parent_id"] : 0;
                    $user->source = isset($userData["source"]) ? $userData["source"] : \Yii::$app->source;
                    if ($user->save() === false) {
                        \Yii::error("userRegister ".var_export($user->getErrors(),true));
                        throw new Exception("用户新增失败");
                    }
                    $Parent = (new UserParentModel()) -> getUserParentData($user -> attributes['id']);
                    if(empty($Parent)){
                        if(!empty($parent_id)){
                            $parent_data = new UserParentModel();
                            $parent_data -> mall_id = \Yii::$app->mall->id;
                            $parent_data -> user_id = $user -> attributes['id'];
                            $parent_data -> parent_id = $parent_id;
                            $parent_data -> updated_at = time();
                            $parent_data -> created_at = time();
                            $parent_data -> deleted_at = time();
                            $parent_data -> is_delete = 0;
                            $parent_data -> level = 1;
                            $parent_data -> save();
                            $UserChildren = new UserChildrenModel();
                            $UserChildren -> id = null;
                            $UserChildren -> mall_id = \Yii::$app->mall->id;
                            $UserChildren -> user_id = $parent_id;
                            $UserChildren -> child_id = $user -> attributes['id'];
                            $UserChildren -> level = 1;
                            $UserChildren -> created_at = time();
                            $UserChildren -> updated_at = time();
                            $UserChildren -> deleted_at = 0;
                            $UserChildren -> is_delete = 0;
                            $UserChildren -> save();
                        }
                    }
                }
            }
            $userInfoModel = new UserInfo();
            $userInfoModel->mall_id = \Yii::$app->mall->id;
            $userInfoModel->mch_id = 0;
            $userInfoModel->user_id = $user->id;
            $userInfoModel->unionid = isset($userData["unionid"]) ? $userData["unionid"] : "";
            $userInfoModel->openid = isset($userData["openid"]) ? $userData["openid"] : "";
            $userInfoModel->platform_data = isset($userData["platform_data"]) ? $userData["platform_data"] : "";
            $userInfoModel->platform = $appPlatform;
            if ($userInfoModel->save() === false) {
                \Yii::error("userRegister ".var_export($userInfoModel->getErrors(),true));
                throw new Exception("用户信息新增失败");
            }
            $transaction->commit();
            return $user;
        }catch (\Exception $ex){
            $message = CommonLogic::getExceptionMessage($ex);
            \Yii::error("userRegister error ".$message);
            $transaction->rollBack();
            return false;
        }
    }

    /**
     * 检测用户手机号是否存在
     * @param $phone
     * @return \app\models\User|bool|null
     */
    public static function checkUserMobileIsExist($phone){
        $result = User::getOneData(["mobile" => $phone,'is_delete' => 0]);
        if(!empty($result)){
            return $result;
        }
        return false;
    }

    /**
     * 检测用户unionid是否存在
     * @param $unionId
     * @return array|bool|UserInfo|null
     */
    public static function checkUserUnionidIsExist($unionId){
        $result = UserInfo::getOneUserInfo(["unionid" => $unionId,'is_delete' => 0,'user' => 1]);
        if(!empty($result)){
            return $result;
        }
        return false;
    }

    /**
     * 加载用户字段数据
     * @param $userInfo
     * @return array
     */
    public static function loadUserFields($userInfo){
        $registerData = [];
        $registerData["nickname"] = isset($userInfo["nickname"]) ? $userInfo["nickname"] : "";
        $registerData["openid"] = $userInfo["openid"];
        $registerData["avatar_url"] = isset($userInfo["headimgurl"]) ? $userInfo["headimgurl"] : "";
        $registerData["unionid"] = isset($userInfo["unionid"]) ? $userInfo["unionid"] : "";
        $registerData["platform"] = empty(\Yii::$app->appPlatform) ? User::PLATFORM_WECHAT : \Yii::$app->appPlatform;
        $registerData["source"] = empty(\Yii::$app->source) ? 0 : \Yii::$app->source;
        $registerData["platform_data"] = json_encode($userInfo);
        $registerData["password"] = "jx888888";
        $registerData["mall_id"] = \Yii::$app->mall->id;
        $registerData["mobile"] = isset($userInfo["mobile"]) ? $userInfo["mobile"] : "";
        $registerData["username"] = isset($userInfo["username"]) ? $userInfo["username"] : "wechat_user";
        return $registerData;
    }

    /**
     * 检测是否授过权
     * @param $userData
     * @param $userId
     * @return User|array|null
     */
    public static function checkIsAuthorized($userData,$userId = 0){
        \Yii::warning("checkIsAuthorized userId={$userId} userData:".var_export($userData,true));
        $returnData = [];
        try{
            $platform = isset($userData["platform"]) ? $userData["platform"] : \Yii::$app->appPlatform;
            $params = ["mall_id"=>\Yii::$app->mall->id,"is_delete" => User::IS_DELETE_NO,"platform" => $platform];
//            if($platform == User::PLATFORM_WECHAT){
//                $params["openid"] = $userData["openid"];
//            }else{
//                if(isset($userData["unionid"]) && !empty($userData["unionid"])){
//                    $params["unionid"] = $userData["unionid"];
//                }
//                $params["openid"] = $userData["openid"];
//            }
            $where['openid'] = $userData['openid'];
            $userInfo = UserInfo::getOneUserInfo($where);
            if(!empty($userData['unionid'])){
                  if(empty($userInfo['unionid'])){
                $db = \yii::$app->db; 
                $db -> createCommand("update jxmall_user_info set unionid = '{$userData['unionid']}' where openid = '{$userData['openid']}'") -> execute();
                 }
            }

            //如果有unionid，则用unionid同步用户数据
            if(isset($userData["unionid"]) && !empty($userData["unionid"])){
                $params["unionid"] = $userData["unionid"];
            }else{
                $params["openid"] = $userData["openid"];
            }
            \Yii::warning("checkIsAuthorized params:".var_export($params,true));
            /** @var UserInfo $userInfo */
            $userInfo = UserInfo::getOneUserInfo($params);
            \Yii::warning("checkIsAuthorized userInfo:".var_export($userInfo,true));
            //先用h5登录之后，再进行授权的情况
            if(!empty($userId) && empty($userInfo)){
                $userInfo = UserInfo::getOneUserInfo(["mall_id"=>\Yii::$app->mall->id,"user_id" => $userId,"platform" => $platform,"is_delete" => User::IS_DELETE_NO]);
                \Yii::warning("checkIsAuthorized userInfo2:".var_export($userInfo,true));
                if(!empty($userInfo)){
                    $userInfo->unionid = isset($userData["unionid"]) && !empty($userData["unionid"]) ? $userData["unionid"] : "";
                    $userInfo->openid = $userData["openid"];
                    if(!$userInfo->save()){
                        \Yii::error("checkIsAuthorized auth fail userInfo=".var_export($userInfo,true));
                    }else{
                        $returnData = $userInfo;
                    }
                }
            }else if(!empty($userInfo)){
                $returnData = User::findIdentity($userInfo->user_id);
            }
        }catch (\Exception $ex){
            \Yii::error("checkIsAuthorized error:".CommonLogic::getExceptionMessage($ex));
        }
        return $returnData;
    }

    /**
     * 获取当前平台用户信息
     * @return array|\yii\db\ActiveRecord|null
     */
    public static function getPlatformUserInfo(){
        return UserInfo::getOneUserInfo(["user_id" => \Yii::$app->user->id,"platform" => \Yii::$app->appPlatform]);
    }

    /**
     * 获取三层父级id
     * @param $user_id
     * @return array
     */
    public static function getUserThreeParentIds($user_id){
        $user = new User();
        $user->id = $user_id;
        $user->mall_id = \Yii::$app->mall->id;
        $parents = $user->getParentList();
        $returnData = [];
        /** @var UserParent $par */
        foreach ($parents as $par){
            $level = $par->level;
            $returnData[$level] = $par->attributes;
        }
        return $returnData;
    }

    /**
     * 获取用户团队的所有成员
     * @param $user_id
     * @param $key
     * @return array
     */
    public static function getUserTeamAllData($user_id,$key = "id"){
        $user = new User();
        $user->id = $user_id;
        $user->mall_id = \Yii::$app->mall->id;
        $parents = $user->getParentList();
        $returnData = [];
        $parentData = [];
        $childData = [];
        if(!empty($parents)){
            /** @var UserParent $parent */
            foreach ($parents as $parent){
                if($key == "id"){
                    $parentData[] = $parent->attributes["parent_id"];
                }else{
                    $parentData[] = $parent->parent;
                }
            }
        }
        $childs = $user->getChildList();
        if(!empty($childs)){
            /** @var UserChildren $child */
            foreach ($childs as $child){
                if($key == "id"){
                    $childData[] = $child->attributes["child_id"];
                }else{
                    $childData[] = $child->children;
                }
            }
        }
        $returnData["team_list"] = array_merge($parentData,$childData);
        $returnData["parent_list"] = $parentData;
        $returnData["child_list"] = $childData;
        return $returnData;
    }

    /**
     * 获取等级下的所有成员
     * @param $teamList
     * @return array
     */
    public static function getUserTeamLevelTotal($teamList){
        $returnData = [];
        if(!empty($teamList)){
            foreach ($teamList as $value){
                $returnData[$value["level"]][] = $value;
            }
        }

        return $returnData;
    }

    /**
     * 获取直推和间推数据
     * @param $data
     * @return array
     */
    public static function getStatUserPushTotal($data = []){
        $directPushList = $spacePushList = $returnData = [];
        $params = [];
        if(!empty($data)){
            $params["user_id"] = $data["user_id"];
        }
        if(isset($data["keywords"])){
            $params["keywords"] = $data["keywords"];
        }
        //直推人数
        $params["flag"] = 1;
        $directPushCount = self::getUserTeamPushList($params,"count");
        $directPushTotal = empty($directPushCount) ? 0 : $directPushCount;
        //间推人数
        $params["flag"] = 2;
        $spacePushCount = self::getUserTeamPushList($params,"count");
        $spacePushTotal = empty($spacePushCount) ? 0 : $spacePushCount;
        $returnData["direct_push_total"] = intval($directPushTotal);
        $returnData["space_push_total"] = intval($spacePushTotal);
        return $returnData;
    }

    /**
     * 获取团队直推或间推列表
     * @param $data data.flag 1直推2间推
     * @param string $type =count 返回数量
     * @return array|int|string|\yii\db\ActiveRecord[]
     */
    public static function getUserTeamPushList($data,$type = "all"){
        $level = $data["flag"];
        $userId = isset($data["user_id"]) && !empty($data["user_id"]) ? $data["user_id"] : \Yii::$app->user->id;
        $query = UserParent::find()->alias("up")->where(["up.mall_id" => \Yii::$app->mall->id,"up.parent_id" => $userId,"up.is_delete" => 0]);
        $selectFields = ["up.*"];
        if(isset($data["group"])){
            $selectFields = ["up.user_id"];
        }
        //搜索用户昵称
        if(isset($params["keywords"]) && !empty($params["keywords"])){
            $query->leftJoin(['u' => User::tableName()], 'u.id = up.user_id');
            $query->andWhere(["like","u.nickname",$data["keywords"]]);

            $userFields = ["u.nickname","u.avatar_url","u.mobile"];
            $selectFields = array_merge($selectFields,$userFields);
        }
        if($level == 1){
            $query = $query->andWhere(["up.level" => $level]);
        }else{
            $query = $query->andWhere([">","up.level" , 1]);
        }
        if($type == "count"){
            return $query->count();
        }
        $isPagination = false;
        if(isset($data["limit"]) && isset($data["page"])){
            $query = $query->page($pagination, $data["limit"],$data["page"]);
            $isPagination = true;
        }
        $query = $query->select($selectFields);
        if(!isset($params["keywords"]) || empty($params["keywords"])){
            $query->with(["user"]);
        }
        if(isset($data["group"])){
            $query->groupBy($data["group"]);
        }
        $list = $query->asArray()->all();
        $returnData = [];
        $returnData["list"] = $list;
        $returnData["pagination"] = $isPagination ? (new BaseModel())->getPaginationInfo($pagination) : [];
        return $returnData;
    }

    /**
     * 获取所有下级id
     * @param $key 获取指定key数组
     * @param $userId
     * @param $mallId
     * @return array
     */
    public static function getAllChildIdsList($key = "id",$userId = 0,$mallId = 0){
        $data = [];
        $user = new User();
        $user->id = ($userId == 0 ? \Yii::$app->user->id : $userId);
        $user->mall_id = ($mallId == 0 ? \Yii::$app->mall->id : $mallId);
        $childs = $user->getChildList();
        if(!empty($childs)){
            $childIds = ArrayHelper::toArray($childs);
            if($key == "id"){
                foreach ($childIds as $child) {
                    $data[] = $child["child_id"];
                }
            }else{
                $data = $childIds;
            }
        }
        return $data;
    }

    /**
     * 获取我的团队订单相关数据
     * @param $teamUserIds
     * @param $params 查询条件
     * @return array
     */
    public static function getUserTeamOrderStatInfo($teamUserIds,$params = []){
        $commonOrderModel = new CommonOrder();
        $commonOrderModel->mall_id = \Yii::$app->mall->id;
        $params["more_user_id"] = $teamUserIds;
        $orderCount = $commonOrderModel->getList($params, "count");
        $orderTotal = $commonOrderModel->getList($params, "sum");
        $userStatData = [];
        $userStatData["team_order_count"] = intval($orderCount);
        $userStatData["team_order_total"] = empty($orderTotal) ? 0 : floatval($orderTotal);
        return $userStatData;
    }

    /**
     * 获取下级的下单人数
     * @param $teamUserIds
     * @param $params 查询条件
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getUseTeamOrderPeopleTotal($teamUserIds,$params = []){
        $commonOrderModel = new CommonOrder();
        $commonOrderModel->mall_id = \Yii::$app->mall->id;
        $params["more_user_id"] = $teamUserIds;
        $params["group_by"] = "user_id";
        $orderPeopleTotal = $commonOrderModel->getList($params, "count");
        return $orderPeopleTotal;
    }

}
