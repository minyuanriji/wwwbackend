<?php

namespace app\plugins\mch\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Store;
use app\models\User;
use app\plugins\mch\models\Mch;

class MchReviewForm extends BaseModel
{
    public $page;
    public $id;
    public $review_status;
    public $keyword;
    public $is_special;

    public function rules()
    {
        return [
            [['id', 'review_status', 'is_special'], 'integer'],
            [['keyword'], 'string'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function getList()
    {
        $query = Mch::find()->alias("m")->where([
            'm.mall_id' => \Yii::$app->mall->id,
            'm.is_delete' => 0,
            'm.review_status' => $this->review_status
        ]);
        $query->leftJoin(["u" => User::tableName()], "u.id=m.user_id");
        $query->leftJoin(["p" => User::tableName()], "p.id=u.parent_id");

        if ($this->keyword) {
            $mchIds = Store::find()->where(['like', 'name', $this->keyword])->select('mch_id');
            $query->andWhere(['m.id' => $mchIds]);
        }
        if ($this->is_special) {
            $query->andWhere(['m.is_special' => $this->is_special]);
        }

        $list = $query->select([
            "m.id", "m.realname", "m.mobile", "m.created_at", "m.user_id", "m.is_special", "m.special_rate", "m.special_rate_remark",
            "p.id as parent_id", "p.nickname as parent_nickname",
            "p.mobile as parent_mobile", "p.role_type as parent_role_type"
        ])
            ->with([
                'user' => function ($query) {
                    $query->select('id, nickname, avatar_url,');
                },
                'store' => function ($query) {
                    $query->select('id, mch_id, cover_url, name');
                }
            ])
            ->orderBy(['m.created_at' => SORT_DESC])
            ->page($pagination)->asArray()->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function getDetail()
    {
        try {
            $detail = Mch::find()->where([
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])->with('user.userInfo')->asArray()->one();
            if (!$detail) {
                throw new \Exception('商户不存在');
            }

            $detail['address'] = \Yii::$app->serializer->decode($detail['address']);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function destroy()
    {
        try {
            $detail = Mch::find()->where([
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])->one();
            if (!$detail) {
                throw new \Exception('商户不存在');
            }

            $detail->is_delete = 1;
            $res = $detail->save();
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($detail));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
