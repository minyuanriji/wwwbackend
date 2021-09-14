<?php

namespace app\plugins\giftpacks\forms\mall\order;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\IntegralLog;
use app\models\User;
use app\plugins\commission\models\CommissionGiftpacksPriceLog;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksOrder;

class GiftpacksOrderListForm extends BaseModel
{
    public $page;
    public $keyword;
    public $start_time;
    public $end_time;
    public $status;

    public function rules()
    {
        return [
            [['page'], 'integer'],
            [['keyword', 'status'], 'string'],
            [['start_time', 'end_time'], 'safe']
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = GiftpacksOrder::find()->alias('go')->where(["go.is_delete" => 0])
                    ->leftJoin(["g" => Giftpacks::tableName()], "go.pack_id = g.id")
                    ->leftJoin(["u" => User::tableName()], "go.user_id = u.id");

            if (!empty($this->keyword)) {
                $query->andWhere([
                    'or',
                    ["LIKE", "go.id", $this->keyword],
                    ["LIKE", "go.order_sn", $this->keyword],
                    ["LIKE", "g.title", $this->keyword],
                ]);
            }

            if ($this->start_time && $this->end_time) {
                $query->andWhere([
                    'and',
                    ['>=', 'go.pay_at', $this->start_time],
                    ['<=', 'go.pay_at', $this->end_time],
                ]);
            }

            if ($this->status) {
                $query->keyword($this->status == 'paid', ['go.pay_status' => $this->status])
                    ->keyword($this->status == 'refund', ['go.pay_status' => $this->status])
                    ->keyword($this->status == 'refunding', ['go.pay_status' => $this->status])
                    ->keyword($this->status == 'unpaid', ['go.pay_status' => $this->status]);
            }

            $select = ['go.*', "g.title", "g.cover_pic", "g.descript", "g.price", "g.integral_enable", "g.integral_give_num", "g.score_enable", "g.score_give_settings", "u.nickname"];

            $list = $query->select($select)->orderBy("go.id DESC")->page($pagination)->asArray()->all();
            if ($list) {
                foreach ($list as &$value) {
                    if ($value['score_give_settings']) {
                        $value['score_give_settings'] = json_decode($value['score_give_settings'], true);
                    }
                    $value['share_profit'] = CommissionGiftpacksPriceLog::find()->alias('cg')
                        ->leftJoin(["u" => User::tableName()], "cg.user_id = u.id")
                        ->where(['order_id' => $value['id']])
                        ->select(['cg.id', 'cg.order_id', 'cg.pack_id', 'cg.user_id', 'cg.price', 'cg.status', 'u.nickname', ])
                        ->asArray()->all();

                    if ($value['integral_enable']) {
                        $value['is_integral'] = IntegralLog::findOne(['source_type' => 'giftpacks_order', 'source_id' => $value['id']]) ? 1 : 0;
                    } else {
                        $value['is_integral'] = 0;
                    }
                }
            }

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