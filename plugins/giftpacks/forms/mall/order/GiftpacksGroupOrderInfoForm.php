<?php

namespace app\plugins\giftpacks\forms\mall\order;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksGroup;
use app\plugins\giftpacks\models\GiftpacksGroupPayOrder;

class GiftpacksGroupOrderInfoForm extends BaseModel
{
    public $id;

    public function rules()
    {
        return [
            [['id'], 'integer']
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $query = GiftpacksGroupPayOrder::find()->alias('ggp')
                    ->leftJoin(["gg" => GiftpacksGroup::tableName()], "ggp.group_id = gg.id")
                    ->leftJoin(["g" => Giftpacks::tableName()], "gg.pack_id = g.id")
                    ->leftJoin(["u" => User::tableName()], "ggp.user_id = u.id");
            $select = ['ggp.*', "gg.user_id as head_user_id", "u.nickname", "u.avatar_url", "g.title", "g.cover_pic"];
            $list = $query->where(['ggp.group_id' => $this->id, 'ggp.mall_id' => \Yii::$app->mall->id])
                ->select($select)
                ->orderBy("ggp.id DESC")
                ->page($pagination)
                ->asArray()->all();
            return $this->returnApiResultData(
                ApiCode::CODE_SUCCESS,
                '',
                [
                    'list' => $list ?: [],
                    'pagination' => $pagination
                ]
            );
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }
}