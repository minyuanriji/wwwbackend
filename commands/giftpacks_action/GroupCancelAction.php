<?php

namespace app\commands\giftpacks_action;

use app\models\Mall;
use app\plugins\giftpacks\forms\common\GiftpacksGroupCancelRefundProcessForm;
use app\plugins\giftpacks\forms\common\GiftpacksGroupRefundProcessForm;
use app\plugins\giftpacks\models\GiftpacksGroup;
use yii\base\Action;

class GroupCancelAction extends Action{

    public function run(){
        \Yii::$app->mall = Mall::findOne(5);

        while(true) {

            //查询已到期未拼成功的拼单
            $group = GiftpacksGroup::find()->andWhere([
                "AND",
                ["status" => "sharing"],
                "user_num < need_num",
                ["<", "expired_at", time()]
            ])->orderBy("updated_at ASC")->one();

            if(!$group){
                sleep(5);
                continue;
            }

            //更新日期
            $group->updated_at = time();
            $group->save();

            $t = \Yii::$app->db->beginTransaction();
            try {

                GiftpacksGroupCancelRefundProcessForm::refund($group);

                $t->commit();

                $this->controller->commandOut("[ID:".$group->id."]拼单取消处理成功");

            }catch (\Exception $e){
                $t->rollBack();
                $this->controller->commandOut($e->getMessage());
            }
        }
    }

}