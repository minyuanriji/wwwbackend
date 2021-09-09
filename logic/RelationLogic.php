<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-15
 * Time: 10:11
 */

namespace app\logic;

use app\component\jobs\ParentChangeJob;
use app\events\TagEvent;
use app\handlers\TagHandler;
use app\helpers\ArrayHelper;
use app\models\CommonOrder;
use app\models\ErrorLog;
use app\models\RelationSetting;
use app\models\User;
use app\models\UserRelationshipLink;
use app\plugins\business_card\jobs\BusinessCardCustomerJob;
use Exception;

class RelationLogic extends BaseLogic
{
    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-15
     * @Time: 10:55
     * @Note:
     * @param User $user
     * @param $parent_id
     * @param int $is_manual
     * @return bool
     * @throws Exception
     */
    public static function bindParent($user, $parent_id, $is_manual = 0)
    {

        if($user->id == 9 || $user->parent_id == $parent_id)
            return;

        $relation = RelationSetting::findOne(['mall_id' => \Yii::$app->mall->id, 'use_relation' => 1, 'is_delete' => 0]);
        if (!$relation) {
            throw new Exception('未启用关系链'.$parent_id);
        }

        if($user->parent_id && $user->parent_id != 9){
            throw new Exception('用户已存在上级');
        }

        if ($parent_id == $user->id) {
            throw new Exception('自己不能绑定自己'.$parent_id);
        }

        $parent = User::findOne($parent_id);
        
        if (!$parent) {
            throw new Exception('绑定的上级用户不存在'.$parent_id);
        }

        $parentLink = UserRelationshipLink::findOne(["user_id" => $parent->id]);
        if(!$parentLink){
            throw new Exception('上级关系链异常，ID：' . $parent->id);
        }

        $userLink = UserRelationshipLink::findOne(["user_id" => $user->id]);
        if(!$userLink){
            throw new Exception('用户关系链异常，ID：' . $user->id);
        }

        if($parentLink->left > $userLink->left && $parentLink->right < $userLink->right){
            throw new \Exception("上级推荐人不能变更为团队下级，必须是平级和上级");
        }

        /*
        if (!$parent->is_inviter) {
            throw new Exception('绑定的上级用户没有推广资格'.$parent_id);
        }
        
        if (!$is_manual) {
            if ($relation->become_child_way == RelationSetting::BECOME_CHILD_WAY_FIRST_ORDER) { //首次下单
                $order = CommonOrder::findOne(['user_id' => $user->id, 'mall_id' => \Yii::$app->mall->id]);
                if (empty($order)) {
                    throw new Exception('不满足成为下级的条件！user_id='.$user->id.";mall_id=".\Yii::$app->mall->id.';parent_id='.$parent_id);
                }
            }
            if ($relation->become_child_way == RelationSetting::BECOME_CHILD_WAY_FIRST_PAY) {//首次付款
                $order = CommonOrder::findOne(['user_id' => $user->id, 'mall_id' => \Yii::$app->mall->id, 'is_pay' => CommonOrder::STATUS_IS_PAY]);
                if (empty($order)) {
                    throw new Exception('不满足成为下级的条件！！ user_id='.$user->id.";mall_id=".\Yii::$app->mall->id.';parent_id='.$parent_id);
                }
            }
        }*/

        //如果用户已有关系链，删除
        /*UserRelationshipLink::deleteAll([
            "user_id" => $user->id
        ]);*/

        $user->parent_id = $parent_id;
        $user->junior_at = time();

        if($user->save() !== false){
            throw new Exception('绑定异常');
        }

        return true;
    }

    


    /**
     * 改变用户表中的三层父级id
     * @param $user_id
     */
    public static function changeUserThreeParentId($user_id){
        \Yii::warning("RelationLogic start changeUserParentInfo user_id={$user_id}");
        try{
            /** @var User $user */
            $user = User::find()->where(['id' => $user_id])->one();
            $parent_ids = UserLogic::getUserThreeParentIds($user_id);
            \Yii::warning("RelationLogic changeUserParentInfo parent_ids=".var_export($parent_ids,true));
            $user->second_parent_id = isset($parent_ids[2]["parent_id"]) ? $parent_ids[2]["parent_id"] : 0;
            $user->third_parent_id = isset($parent_ids[3]["parent_id"]) ? $parent_ids[3]["parent_id"] : 0;
            $res = $user->save();
            if($res === false){
                throw new \Exception("用户上级更新失败");
            }
            //获取所有下级
            $childList = $user->getChildList();
            \Yii::warning("RelationLogic changeUserParentInfo childList=".var_export($childList,true));
            if(!empty($childList)){
                foreach ($childList as $ch){
                    /** @var User $userChilds */
                    $userChilds = User::getOneData(["id" => $ch["child_id"]]);
                    $parent_ids = UserLogic::getUserThreeParentIds($ch["child_id"]);
                    //$userChilds->parent_id = isset($parent_ids[1]["parent_id"]) ? $parent_ids[1]["parent_id"] : 0;
                    //$userChilds->second_parent_id = isset($parent_ids[2]["parent_id"]) ? $parent_ids[2]["parent_id"] : 0;
                    //$userChilds->third_parent_id = isset($parent_ids[3]["parent_id"]) ? $parent_ids[3]["parent_id"] : 0;
                    $res = $userChilds->save();
                    if($res === false){
                        throw new \Exception("用户上级更新失败！");
                    }
                }
            }
            \Yii::warning("RelationLogic end changeUserParentInfo user_id={$user_id}");
        }catch (\Exception $ex){
            \Yii::error("RelationLogic end changeUserParentInfo error File:".$ex->getFile().";Line:".$ex->getLine().";message:".$ex->getMessage());
        }
    }

    /**
     * 触发插件任务
     * @param $userId
     * @param $parentId
     * @param $mall_id
     */
    public static function pluginsHandler($userId,$parentId,$mall_id){
        try{
            \Yii::$app->queue->delay(0)->push(new BusinessCardCustomerJob([
                'user_id' => $userId,
                'mall_id' => $mall_id,
                'parent_id' => $parentId,
            ]));
        }catch (\Exception $ex){
            \Yii::error("pluginsHandler 名片插件未开启");
        }
    }
}