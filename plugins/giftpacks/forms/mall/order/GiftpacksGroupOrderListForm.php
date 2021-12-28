<?php

namespace app\plugins\giftpacks\forms\mall\order;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksGroup;
use app\plugins\giftpacks\models\GiftpacksGroupPayOrder;

class GiftpacksGroupOrderListForm extends BaseModel
{
    public $page;
    public $keyword;
    public $kw_type;
    public $start_time;
    public $end_time;
    public $status;

    public function rules()
    {
        return [
            [['page'], 'integer'],
            [['keyword', 'status', 'kw_type'], 'string'],
            [['start_time', 'end_time'], 'safe']
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $query = GiftpacksGroup::find()->alias('gg')
                    ->leftJoin(["g" => Giftpacks::tableName()], "gg.pack_id = g.id")
                    ->leftJoin(["u" => User::tableName()], "gg.user_id = u.id");

            if (!empty($this->keyword) && !empty($this->kw_type)) {
                switch ($this->kw_type)
                {
                    case 'group_id':
                        $query->andWhere(['gg.id' => $this->keyword]);
                        break;
                    case 'gift_name':
                        $query->andWhere(["LIKE", "g.title", $this->keyword]);
                        break;
                    case 'user_id':
                        $group_ids = GiftpacksGroupPayOrder::find()->where(['user_id' => $this->keyword])->select('group_id');
                        $query->andWhere(["in", "gg.id", $group_ids]);
                        break;
                    default:
                }
            }

            if ($this->start_time && $this->end_time) {
                $query->andWhere([
                    'and',
                    ['>=', 'gg.created_at', strtotime($this->start_time)],
                    ['<=', 'gg.created_at', strtotime($this->end_time)],
                ]);
            }

            if ($this->status) {
                $query->keyword($this->status == 'success', ['gg.status' => $this->status])
                    ->keyword($this->status == 'closed', ['gg.status' => $this->status])
                    ->keyword($this->status == 'sharing', ['gg.status' => $this->status]);
            }

            $select = ['gg.*', "g.title", "g.cover_pic", "g.descript", "g.price", "u.nickname", "u.avatar_url"];

            $list = $query->select($select)->orderBy("gg.id DESC")->page($pagination)->asArray()->all();
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