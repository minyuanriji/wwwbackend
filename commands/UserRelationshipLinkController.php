<?php
namespace app\commands;

use app\models\User;
use app\models\UserRelationshipLink;

class UserRelationshipLinkController extends BaseCommandController {

    /**
     * 维护用户关系链
     */
    public function actionMaintantJob(){

        $this->mutiKill(); //只能只有一个维护服务

        echo date("Y-m-d H:i:s") . " 关系链守候程序启动...完成\n";

        while (true){

            $this->sleep(1);

            /**
             * ================================================
             *  丢失父级
             * ------------------------------------------------
             */
            if($this->maintantMissParent()){
                continue;
            }

            /**
             * ================================================
             *  新增
             * ------------------------------------------------
             */
            if($this->maintantInsert()){
                continue;
            }

            /**
             * ================================================
             *  修改
             * ------------------------------------------------
             */
            if($this->maintantModified()){
                continue;
            }

        }
    }

    /**
     * 丢失父级
     */
    private function maintantMissParent(){
        \Yii::$app->db->schema->refresh();
        try {
            //获取一个待操作用户
            $query = UserRelationshipLink::find()->alias("c");
            $query->leftJoin("{{%user_relationship_link}} p", "p.user_id=c.parent_id");
            $query->leftJoin("{{%user}} u", "u.id=p.user_id");
            $query->andWhere([
                "AND",
                "c.parent_id > 0",
                "p.user_id IS NULL",
                "u.id > 0"
            ]);
            $userLink = $query->orderBy("c.left ASC")->one();
            if(!$userLink){
                return false;
            }

            $this->commandOut("user[" . $userLink->user_id . "] miss parent[".$userLink->parent_id."]");

            //把缺失父级的所有下级删掉
            $whereStr = "`left` >= '".$userLink->left."' AND `right` <= '".$userLink->right."'";
            UserRelationshipLink::deleteAll($whereStr);

        }catch (\Exception $e){
            $this->commandOut($e->getMessage());
        }

        return true;
    }

    /**
     * 新增关系
     */
    private function maintantInsert(){

        \Yii::$app->db->schema->refresh();
        try {

            //获取一个待操作用户
            $query = User::find()->alias("u");
            $query->leftJoin("{{%user_relationship_link}} url", "url.user_id=u.id");
            $query->andWhere("url.user_id IS NULL");
            $query->orderBy("u.id ASC");
            $user = $query->one();

            if(!$user) {
                return false;
            }

            $this->commandOut("新增用户：".$user->id."关系链记录");

            //找出用户所有上级
            $userList = [$user];
            while(true){
                $parent = null;
                if($user->parent_id && $user->id != $user->parent_id){
                    $parent = User::findOne($user->parent_id);
                }

                if(!$parent) break;

                array_unshift($userList, $parent);

                $user = $parent;
            }

            $userList[0]->parent_id = 0;

            $trans = \Yii::$app->getDb()->beginTransaction();
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
                        $parentLink = UserRelationshipLink::findOne(["user_id" => $parent->id]);
                        if(!$parentLink){
                            throw new \Exception("关系链异常");
                        }

                        $left  = $parentLink->right;
                        $right = $left + 1;

                        UserRelationshipLink::updateAllCounters(["left" => 2, "right" => 2],
                            "`left` > '".$parentLink->right."'");

                        UserRelationshipLink::updateAllCounters(["right" => 2],
                            "`left` <= '".$parentLink->left."' AND `right` >= '".$parentLink->right."'");

                    }else{
                        $maxLink = UserRelationshipLink::find()->select(["right"])->orderBy("`right` DESC")->limit(1)->one();
                        $left = $maxLink ? ($maxLink->right + 1) : 1;
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

                $trans->commit();
            }catch (\Exception $e){
                $trans->rollBack();
                throw $e;
            }

        }catch (\Exception $e){
            $this->commandOut($e->getMessage());
        }

        return true;
    }

    /**
     * 修改关系
     */
    private function maintantModified(){

        \Yii::$app->db->schema->refresh();

        $editLink = null;

        $trans = \Yii::$app->db->beginTransaction();
        try {
            //获取一个待操作用户
            $query = User::find()->alias("u");
            $query->leftJoin("{{%user_relationship_link}} url", "url.user_id=u.id");
            $query->leftJoin("{{%user}} new_p", "new_p.id=u.parent_id");
            $query->andWhere([
                "AND",
                "url.user_id IS NOT NULL",
                "u.parent_id <> url.parent_id",
                "u.id <> u.parent_id",
                "(new_p.id IS NOT NULL OR u.parent_id=0)"
            ]);
            $query->orderBy("u.id ASC");
            $user = $query->one();

            if(!$user){
                $trans->rollBack();
                return false;
            };

            $this->commandOut("修改用户：".$user->id."关系链记录");

            $editLink = UserRelationshipLink::findOne(["user_id" => $user->id]);
            if(!$editLink){
                throw new \Exception("关系链获取失败");
            }

            //进行收缩处理
            UserRelationshipLink::updateAll([
                "is_delete"     => 1,
                "delete_reason" => "parent changed"
            ], "`left` >= '".$editLink->left."' AND `right` <= '".$editLink->right."'");

            $gap  = $editLink->right - $editLink->left + 1;
            UserRelationshipLink::updateAllCounters(["left" => -1 * $gap, "right" => -1 * $gap],
                "`left` > '".$editLink->right."'");
            if($editLink->parent_id){
                UserRelationshipLink::updateAllCounters(["right" => -1 * $gap],
                    "`left` < '".$editLink->left."' AND `right` > '".$editLink->right."'");
            }

            //新父级关系记录
            $newParentLink = null;
            if($user->parent_id && $user->id != $user->parent_id){
                $newParent = User::findOne($user->parent_id);
                if($newParent){
                    $newParentLink = UserRelationshipLink::findOne(["user_id" => $user->parent_id]);
                    if(!$newParentLink){
                        throw new \Exception("无法获取父级关系链记录");
                    }
                }else{
                    $user->parent_id = 0;
                }
            }

            //计算出最右值
            if($newParentLink){
                $maxRight = $newParentLink->right;
            }else{
                $maxLink  = UserRelationshipLink::find()->andWhere([
                    "OR",
                    "`left` < '".$editLink->left."'",
                    "`right` > '".$editLink->right."'"
                ])->select(["right"])->orderBy("`right` DESC")->limit(1)->one();
                $maxRight = $maxLink ? ($maxLink->right+1) : 1;
            }

            $diff = $maxRight - $editLink->left;

            //对新的父级关系进行扩展
            UserRelationshipLink::updateAllCounters(["left" => $gap, "right" => $gap],
                "`left` >= '".$maxRight."' AND is_delete=0");
            UserRelationshipLink::updateAllCounters(["right" => $gap],
                "`left` < '".$maxRight."' AND `right` >= '".$maxRight."' AND is_delete=0");

            UserRelationshipLink::updateAllCounters(["left" => $diff, "right" => $diff],
                "`left` >= '".$editLink->left."' AND `right` <= '".$editLink->right."' AND is_delete='1'");

            $editLink = UserRelationshipLink::findOne($editLink->user_id);
            $editLink->parent_id = $user->parent_id;
            if(!$editLink->save()){
                throw new \Exception(json_encode($editLink->getErrors()));
            }

            UserRelationshipLink::updateAll([
                "is_delete"     => 0,
                "delete_reason" => ""
            ], "`left` >= '".$editLink->left."' AND `right` <= '".$editLink->right."'");

            $trans->commit();
        }catch (\Exception $e){
            $trans->rollBack();
            $this->commandOut($e->getMessage() . "\nline" . $e->getLine());
        }

        return true;
    }
}