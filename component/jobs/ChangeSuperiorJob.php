<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 改变上级
 * Author: zal
 * Date: 2020-06-11
 * Time: 16:11
 */

namespace app\component\jobs;

use app\logic\CommonLogic;
use app\logic\UserLogic;
use app\models\Mall;
use app\models\User;
use app\models\UserChildren;
use app\models\UserParent;
use app\plugins\distribution\models\Distribution;
use app\plugins\distribution\models\DistributionSetting;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * @property User $user
 * @property Mall $mall
 */
class ChangeSuperiorJob extends BaseObject implements JobInterface
{
    public $mall;
    public $user;
    public $user_id;
    public $beforeParentId;// 变更前的上级id
    public $parentId; // 变更后的上级id
    private $level;

    public function execute($queue)
    {
        \Yii::warning("---ChangeSuperiorJob start--");
        \Yii::warning("ChangeSuperiorJob execute user_id=".$this->user_id.";parentId=".$this->parentId.";beforeParentId={$this->beforeParentId}");
        if ($this->beforeParentId == $this->parentId) {
            \Yii::warning("更改前父级id与变更后父级id一致");
            return true;
        }
        if ($this->user_id == $this->parentId) {
            \Yii::warning("自己不能设置自己为父级id");
            return true;
        }
        \Yii::$app->setMall(Mall::findOne($this->mall->id));
        if(!CommonLogic::checkIsEnablePlugin()){
            \Yii::warning("未开启分销");
            return true;
        }
        $this->user = User::find()->where(['id' => $this->user_id])->one();

        //获取所有下级
        $childList = $this->user->getChildList();
        //获取用户团队所有成员
        //$userTeamList = UserLogic::getUserTeamAllData($this->beforeParentId);
        $userTeamList = UserLogic::getUserTeamAllData($this->user_id);
        //用户下级中存在变更后的上级id，则直接返回
        if(in_array($this->parentId,$userTeamList["child_list"])){
            \Yii::warning("用户下级中有目标父级id");
            return true;
        }
        $this->level = $level = DistributionSetting::getValueByKey( DistributionSetting::LEVEL);
        \Yii::error('--上级更改--');
        $t = \Yii::$app->db->beginTransaction();
        try {
            $this->changeUserRelation($this->parentId,$this->user_id,$userTeamList["team_list"]);
            $this->updateTotalChild($this->parentId);
            $this->updateTotalChild($this->beforeParentId);
            $this->changeUserParentInfo($childList);
            $t->commit();
            CommonLogic::changeCustomerOperator($this->user_id,$this->parentId,$this->beforeParentId,$this->mall->id);
        } catch (\Exception $exception) {
            $t->rollBack();
            $msg = $exception->getFile().";Line:".$exception->getLine().";message:".$exception->getMessage();
            \Yii::error("ChangeSuperiorJob execute 用户变更上级出错 message:{$msg}");
        }
        \Yii::warning("---ChangeSuperiorJob end--");
    }

    /**
     * 修改分销商的直属下级数量和总下级数量
     * @param $parentId
     * @return bool|mixed
     * @throws \Exception
     *
     */
    private function updateTotalChild($parentId)
    {
        if($parentId > 0){
            /* @var Distribution $parent */
            $parent = Distribution::find()->where(['user_id' => $parentId, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->one();
            if (!$parent) {
                return true;
            }

            $parentTotalChilds = UserChildren::find()->where(["user_id" => $parentId,"is_delete" => 0])->select(["id"])->count();

            if(empty($parentTotalChilds)){
                $parentTotalChilds = 0;
            }

            $parent->total_childs = $parentTotalChilds;

            if (!$parent->save()) {
                throw new \Exception('上级保存失败');
            }
        }

        return true;
    }

    /**
     * 更改用户上下级关系
     * @param int $change_parent_id
     * @param int $user_id
     * @param array $userTeamList
     * @throws \Exception
     */
    protected function changeUserRelation($change_parent_id = 0,$user_id = 0,$userTeamList = []){
        $change_parent_id = empty($change_parent_id) ? $this->parentId : $change_parent_id;
        $user_id = empty($user_id) ? $this->user_id : $user_id;
        \Yii::warning("---ChangeSuperiorJob changeUserRelation start---");
        \Yii::warning("ChangeSuperiorJob changeUserRelation change_parent_id=".$change_parent_id.",user_id=".$user_id);
        try{
            /*** 处理子级start ***/
            //查出目标父级id与用户的对应level值
            //可能目标父级id与用户id不存在，说明不在当前团队中变换，已经在另外一个团队变换了
            $updateParents = UserChildren::find()->where(["mall_id" => $this->mall->id,"user_id"=>$change_parent_id,"child_id"=>$user_id,"is_delete" => 0])->asArray()->one();
            $updateParentLevel = isset($updateParents["level"]) ? $updateParents["level"] : 0;
            $userChilds = UserChildren::find()->where(["or",
                [
                    "mall_id" => $this->mall->id,
                    "user_id"=>$user_id,
                    "is_delete" => 0
                ],[
                    "user_id" => $this->beforeParentId,
                    "child_id"=> $user_id,
                    "is_delete" => 0
                ]])->orderBy("level asc")->asArray()->all();
            \Yii::warning("ChangeSuperiorJob changeUserRelation userChilds=".var_export($userChilds,true));
            //数据不为空，说明有关系链，否则没有关系链，需要重新添加
            if(!empty($userChilds)){
                //被更换用户id的所有上级
                $userChildParents = UserChildren::find()->where(["mall_id" => $this->mall->id,"child_id"=>$user_id,"is_delete" => 0])->orderBy("level asc")->asArray()->all();
                //找第一个子级id，并找出目标父级ID与第一子级id对应的level
                //可能目标父级id与第一子级id不存在，说明不在当前团队中变换，已经在另外一个团队变换了
                //$child_id = $userChilds[0]["child_id"];
                //$userChildrens = UserChildren::find()->where(["mall_id" => $this->mall->id,"user_id"=>$change_parent_id,"child_id"=>$child_id,"is_delete" => 0])->asArray()->one();
                $child_level = $updateParentLevel;
                //改变后两者相差的等级
                $change_level = $child_level - 1;

                //删掉与子级id相关的记录
                //UserChildren::updateAll(["is_delete" => 1,"deleted_at" => time()],["or",["user_id" => $user_id],["child_id" => $user_id]]);
                //删除上一级的绑定关系
                UserChildren::updateAll(["is_delete" => 1,"deleted_at" => time()],["user_id" => $this->beforeParentId,"child_id"=>$user_id]);

                if($this->beforeParentId == 0){
                    //自己上级
                    $self_user_child = UserChildren::find()->where(["mall_id" => $this->mall->id,"user_id" => $change_parent_id,"child_id"=>$user_id,"is_delete" => 0])->one();
                    if(empty($self_user_child)){
                        UserChildren::add($this->mall->id, $change_parent_id, $user_id, 1);
                    }
                }

                //处理子级
                foreach ($userChilds as $child){
                    $childId = $child["child_id"];
                    /** 在同一团队中变换的情况  **/
                    //所有上级关联的子级等级都相应改变
//                    if(!empty($userChangeChildParents)){
//                        //添加目标父级对应的子级记录
//                        $user_change_first_childrens = UserChildren::find()->where(["mall_id" => $this->mall->id,"user_id" => $change_parent_id,"child_id"=>$childId,"is_delete" => 0])->one();
//                        if(empty($user_change_first_childrens)) {
//                            if($child["child_id"] == $user_id){
//                                $child["level"] = 0;
//                            }
//                            $level = $child["level"] + 1;
//                            UserChildren::add($this->mall->id, $change_parent_id, $childId, $level);
//                        }
//                        foreach($userChangeChildParents as $child_cp){
//                            /** @var UserChildren $user_childrens */
//                            $user_change_childrens = UserChildren::find()->where(["mall_id" => $this->mall->id,"user_id"=>$child_cp["user_id"],"child_id"=>$childId,"is_delete" => 0])->one();
//                            if(empty($user_change_childrens)){
//                                //添加目标父级所有上级的子级记录
//                                if($child["child_id"] == $user_id){
//                                    $level = $child_cp["level"] + 1;
//                                }else{
//                                    $level = $child["level"] + 2;
//                                }
//                                UserChildren::add($this->mall->id,$child_cp["user_id"],$childId,$level);
//                            }
//                        }
//                    }
                    //所有上级关联的子级等级都相应改变
                    if(!empty($userChildParents)){
                        foreach($userChildParents as $child_p){
                            //变更的父级id是否大于等于原来父级id等级（小于等级的父级id下的所有子级都删除）
                            if($updateParentLevel <= $child_p["level"] && in_array($change_parent_id,$userTeamList)){
                                /** @var UserChildren $user_childrens */
                                $user_childrens = UserChildren::find()->where(["mall_id" => $this->mall->id,"user_id"=>$child_p["user_id"],"child_id"=>$childId,"is_delete" => 0])->one();
                                if(!empty($user_childrens)){
                                    $user_childrens->level = $user_childrens->level - $change_level;
                                    $user_childrens->save();
                                }
                            }else{
                                UserChildren::updateAll(["is_delete" => 1,"deleted_at" => time()],["mall_id" => $this->mall->id,"user_id" => $child_p["user_id"],"child_id" => $childId]);
                            }
                        }
                    }
                    /** 在另一个团队中变换的情况 **/
                    //更换到新的团队
                    $this->getUsernotInTeamChildrenHandle($change_parent_id,$child_level,$userTeamList,$user_id,$childId,$child["level"]);
                }
                //用户id与目标父级下的所有上级进行绑定
                $this->getUsernotInTeamChildrenHandle($change_parent_id,0,$userTeamList,$user_id,$user_id,1);
            }else{
                UserChildren::updateAll(["is_delete" => 1,"deleted_at" => time()],["or",["user_id" => $user_id],["child_id" => $user_id]]);
                $this->getUsernotInTeamChildrenHandle($change_parent_id,0,$userTeamList,$user_id,$user_id,1);
            }
            /*** 处理子级end ***/
            /*** 处理父级start ***/
            $userParents = UserParent::find()->where(["or",
                [
                    "mall_id" => $this->mall->id,
                    "parent_id" => $user_id,
                    "is_delete" => 0],
                [
                    "user_id" => $user_id,
                    "parent_id" => $this->beforeParentId,
                    "is_delete" => 0
                ]])->asArray()->all();
            \Yii::warning("ChangeSuperiorJob changeUserRelation userParents=".var_export($userParents,true));
            if(!empty($userParents)){
                //查询该用户的所有父级ID
                $userChildParents = UserParent::find()->where(["mall_id" => $this->mall->id,"user_id"=>$user_id,"is_delete" => 0])->asArray()->all();
                //查出用户与目标父级id关联的level
                $parentsData = UserParent::find()->where(["user_id"=>$user_id,"parent_id"=>$change_parent_id,"is_delete" => 0])->asArray()->one();
                $parent_level = isset($parentsData["level"]) ? $parentsData["level"] : 0;
                //改变后两者相差的等级
                $change_level = $parent_level - 1;
                $change_level = $change_level < 0 ? 0 : $change_level;
                //UserChildren::find()->where(["user_id"=>$change_parent_id,"child_id"=>$child_id,"is_delete" => 0])->andWhere([">=","level",$child_level])->asArray()->one();

                //删除与父级id相关的记录
                //UserParent::updateAll(["is_delete" => 1,"deleted_at" => time()],["or",["user_id" => $user_id],["parent_id" => $user_id]]);
                //删除与上级的绑定关系
                UserParent::updateAll(["is_delete" => 1,"deleted_at" => time()],["user_id"=>$user_id,"parent_id" => $this->beforeParentId]);

                if($this->beforeParentId == 0){
                    //自己上级
                    $self_user_parent = UserParent::find()->where(["mall_id" => $this->mall->id,"user_id" => $user_id,"parent_id" => $change_parent_id,"is_delete" => 0])->one();
                    if(empty($self_user_parent)){
                        UserParent::add($this->mall->id,$user_id,$change_parent_id,1);
                    }
                }

                foreach ($userParents as $parents){
                    $parentUserId = $parents["user_id"];
                    if(!empty($userChildParents)){
                        foreach($userChildParents as $parent_p) {
                            //变更的父级id是否大于等于原来父级id等级（小于等级的父级id下的所有子级都删除）
                            if($updateParentLevel <= $parent_p["level"] && in_array($change_parent_id,$userTeamList)) {
                                /** @var UserParent $user_parents */
                                $user_parents = UserParent::find()->where(["mall_id" => $this->mall->id,"user_id" => $parentUserId, "parent_id" => $parent_p["parent_id"], "is_delete" => 0])->one();
                                if (!empty($user_parents)) {
                                    $user_parents->level = $user_parents->level - $change_level;
                                    $user_parents->save();
                                }
                            }else{
                                UserParent::updateAll(["is_delete" => 1,"deleted_at" => time()],["mall_id" => $this->mall->id,"user_id" => $parentUserId,"parent_id" => $parent_p["parent_id"]]);
                            }
                        }
                    }
                    /** 在另一个团队中变换的情况 **/
                    //更换到新的团队
                    $this->getUserNotInTeamParentHandle($change_parent_id,$parent_level,$userTeamList,$user_id,$parents["user_id"],$parents["level"]);
                }
                //用户id与目标父级下的所有上级进行绑定
                $this->getUserNotInTeamParentHandle($change_parent_id,0,$userTeamList,$user_id,$user_id,1);
            }else{
                UserParent::updateAll(["is_delete" => 1,"deleted_at" => time()],["or",["user_id" => $user_id],["parent_id" => $user_id]]);
                $this->getUserNotInTeamParentHandle($change_parent_id,0,$userTeamList,$user_id,$user_id,1);
            }
            /*** 处理父级end ***/
            \Yii::warning("---ChangeSuperiorJob changeUserRelation end---");
        }catch (\Exception $ex){
            $msg = $ex->getFile().";Line:".$ex->getLine().";message:".$ex->getMessage();
            \Yii::error("ChangeSuperiorJob changeUserRelation message:{$msg}");
            throw new \Exception($msg);
        }
    }

    /**
     * 改变用户下所有子级的三层父级id
     * @throws \Exception
     */
    public function changeUserParentInfo($childList){
        $user_id = $this->user_id;
        $parent_id = $this->parentId;
        $user = $this->user;
        $user->parent_id = $parent_id;
        $parent_ids = UserLogic::getUserThreeParentIds($user_id);
        $user->second_parent_id = isset($parent_ids[2]["parent_id"]) ? $parent_ids[2]["parent_id"] : 0;
        $user->third_parent_id = isset($parent_ids[3]["parent_id"]) ? $parent_ids[3]["parent_id"] : 0;
        $res = $user->save();
        if($res === false){
            throw new \Exception("用户上级更新失败");
        }

        if(!empty($childList)){
            foreach ($childList as $ch){
                /** @var User $userChilds */
                $userChilds = User::getOneData(["id" => $ch["child_id"]]);
                $parent_ids = UserLogic::getUserThreeParentIds($ch["child_id"]);
                $userChilds->parent_id = isset($parent_ids[1]["parent_id"]) ? $parent_ids[1]["parent_id"] : 0;
                $userChilds->second_parent_id = isset($parent_ids[2]["parent_id"]) ? $parent_ids[2]["parent_id"] : 0;
                $userChilds->third_parent_id = isset($parent_ids[3]["parent_id"]) ? $parent_ids[3]["parent_id"] : 0;
                $res = $userChilds->save();
                if($res === false){
                    throw new \Exception("用户上级更新失败！");
                }
            }
        }
    }

    private function getUsernotInTeamChildrenHandle($change_parent_id,$child_level,$userTeamList,$user_id,$childId,$childLevel){
        /*** 不在一个团队情况 ***/
        //目标父级id的所有上级
        $userChangeChildParents = UserChildren::find()->where(["mall_id" => $this->mall->id,"child_id"=>$change_parent_id,"is_delete" => 0])->
                                    orderBy("level asc")->asArray()->all();
        \Yii::warning("ChangeSuperiorJob getUsernotInTeamChildrenHandle userChangeChildParents=".var_export($userChangeChildParents,true));
        //更换到新的团队
        if(!empty($userChangeChildParents) && $child_level == 0  && !in_array($change_parent_id,$userTeamList)){
            //添加目标父级对应的子级记录
            $user_change_first_childrens = UserChildren::find()->where(["mall_id" => $this->mall->id,"user_id" => $change_parent_id,"child_id"=>$childId,"is_delete" => 0])->one();
            if(empty($user_change_first_childrens)) {
                if($childId == $user_id){
                    $childLevel = 0;
                }
                $level = $childLevel + 1;
                UserChildren::add($this->mall->id, $change_parent_id, $childId, $level);
            }
            foreach($userChangeChildParents as $child_cp){
                /** @var UserChildren $user_childrens */
                $user_change_childrens = UserChildren::find()->where(["mall_id" => $this->mall->id,"user_id"=>$child_cp["user_id"],"child_id"=>$childId,"is_delete" => 0])->one();
                if(empty($user_change_childrens)){
                    //添加目标父级所有上级的子级记录
                    if($childId == $user_id){
                        $level = $child_cp["level"] + 1;
                    }else{
                        $level = $childLevel + 2;
                    }
                    UserChildren::add($this->mall->id,$child_cp["user_id"],$childId,$level);
                }
            }
        }else if($child_level == 0  && !in_array($change_parent_id,$userTeamList)){
            //添加目标父级对应的子级记录
            $user_change_first_childrens = UserChildren::find()->where(["mall_id" => $this->mall->id,"user_id" => $change_parent_id,"child_id"=>$childId,"is_delete" => 0])->one();
            if(empty($user_change_first_childrens)) {
                if($childId == $user_id){
                    $childLevel = 0;
                }
                $level = $childLevel + 1;
                UserChildren::add($this->mall->id, $change_parent_id, $childId, $level);
            }
        }
    }

    private function getUserNotInTeamParentHandle($change_parent_id,$parent_level,$userTeamList,$user_id,$parentUserId,$parentLevel){
        //目标父级id的所有上级
        $userChangeChildParents = UserParent::find()->where(["mall_id" => $this->mall->id,"user_id"=>$change_parent_id,"is_delete" => 0])->
                                    orderBy("level asc")->asArray()->all();
        /*** 不在一个团队情况 ***/
        //更换到新的团队
        if(!empty($userChangeChildParents) && $parent_level == 0 && !in_array($change_parent_id,$userTeamList)){
            //添加用户对应的父级记录
            $user_change_first_parent = UserParent::find()->where(["mall_id" => $this->mall->id,"user_id" => $parentUserId,"parent_id" => $change_parent_id,"is_delete" => 0])->one();
            if(empty($user_change_first_parent)){
                if($parentUserId == $user_id){
                    $parentLevel = 0;
                }
                $level = $parentLevel + 1;
                UserParent::add($this->mall->id,$parentUserId,$change_parent_id,$level);
            }
            foreach($userChangeChildParents as $parent_cp){
                /** @var UserChildren $user_childrens */
                $user_change_parents = UserParent::find()->where(["mall_id" => $this->mall->id,"user_id" => $parentUserId,"parent_id" => $parent_cp["parent_id"],"is_delete" => 0])->one();
                if(empty($user_change_parents)){
                    //添加目标父级所有上级的子级记录
//                                if($parents["user_id"] == $user_id){
//                                    $level = $parents["level"] + 1;
//                                }else{
//                                    $level = $parents["level"] + 2;
//                                }
//                                UserParent::add($this->mall->id,$parentUserId,$parent_cp["parent_id"],$level);
                    //添加目标父级所有上级的子级记录
                    if($parentUserId == $user_id){
                        $level = $parent_cp["level"] + 1;
                    }else{
                        $level = $parentLevel + 2;
                    }
                    UserParent::add($this->mall->id,$parentUserId,$parent_cp["parent_id"],$level);
                }
            }
        }else if($parent_level == 0 && !in_array($change_parent_id,$userTeamList)){
            //添加用户对应的父级记录
            $user_change_first_parent = UserParent::find()->where(["mall_id" => $this->mall->id,"user_id" => $parentUserId,"parent_id" => $change_parent_id,"is_delete" => 0])->one();
            if(empty($user_change_first_parent)){
                if($parentUserId == $user_id){
                    $parentLevel = 0;
                }
                $level = $parentLevel + 1;
                UserParent::add($this->mall->id,$parentUserId,$change_parent_id,$level);
            }
        }
    }
}
