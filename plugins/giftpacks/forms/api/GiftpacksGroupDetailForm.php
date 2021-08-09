<?php

namespace app\plugins\giftpacks\forms\api;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksGroup;
use app\plugins\giftpacks\models\GiftpacksGroupPayOrder;

class GiftpacksGroupDetailForm extends BaseModel{

    public $group_id;

    public function rules(){
        return [
            [['group_id'], 'required']
        ];
    }

    public function getDetail() {

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $groupInfo = GiftpacksGroup::find()->alias("gg")
                        ->innerJoin(["u" => User::tableName()], "u.id=gg.user_id")
                        ->where(["gg.id" => $this->group_id])
                        ->select([
                            "gg.id", "gg.pack_id", "gg.user_id", "gg.need_num", "gg.user_num",
                            "gg.status", "gg.expired_at", "u.nickname", "u.avatar_url"
                        ])->asArray()->one();
            if(!$groupInfo){
                throw new \Exception("拼单信息不存在");
            }

            $groupInfo['still_need_num'] = intval($groupInfo['need_num']) - intval($groupInfo['user_num']);
            unset($groupInfo['need_num']);
            unset($groupInfo['user_num']);

            //获取大礼包
            $giftpacks = Giftpacks::findOne($groupInfo['pack_id']);
            if(!$giftpacks || $giftpacks->is_delete){
                throw new \Exception("大礼包不存在");
            }

            //获取参与记录
            $joinList = GiftpacksGroupPayOrder::find()->alias("ggpo")
                        ->innerJoin(["u" => User::tableName()], "u.id=ggpo.user_id")
                        ->where(["ggpo.group_id" => $this->group_id, "ggpo.pay_status" => "paid"])
                        ->select([
                            "ggpo.user_id", "u.nickname", "u.avatar_url"
                        ])->asArray()->all();
            if($joinList){
                foreach($joinList as &$row){
                    $row['is_owner'] = $row['user_id'] == $groupInfo['user_id'] ? 1 : 0;
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    "group_info"     => $groupInfo,
                    "join_list"      => $joinList,
                    "giftpacks_info" => GiftpacksDetailForm::detail($giftpacks)
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}