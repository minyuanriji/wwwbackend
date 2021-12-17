<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\shopping_voucher\models\ShoppingVoucherLog;

class ShoppingVoucherLogListForm extends BaseModel{

    public $page;
    public $limit;
    public $start_date;
    public $end_date;
    public $keyword;
    public $kw_type;
    public $source_type;

    public function rules(){
        return [
            [['page', 'limit'], 'integer'],
            [['keyword', 'start_date', 'end_date', 'source_type', 'kw_type'], 'trim'],
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = ShoppingVoucherLog::find()->alias("l")->where([
                'l.mall_id' => \Yii::$app->mall->id
            ])->innerJoin(["u" => User::tableName()], "l.user_id=u.id");

            if ($this->start_date && $this->end_date) {
                $query->andWhere([
                    "AND",
                    ["<", "l.created_at", strtotime($this->end_date)],
                    [">", "l.created_at", strtotime($this->start_date)]
                ]);
            }

            if ($this->source_type) {
                $query->andWhere(['source_type' => $this->source_type]);
            }

            if ($this->keyword && $this->kw_type) {
                switch ($this->kw_type)
                {
                    case "mobile":
                        $query->andWhere(['u.mobile' => $this->keyword]);
                        break;
                    case "user_id":
                        $query->andWhere(['u.id' => $this->keyword]);
                        break;
                    case "nickname":
                        $query->andWhere(['like', 'u.nickname', $this->keyword]);
                        break;
                    default:
                }
            }

            $query->select(["l.*", "u.nickname", "u.role_type", "u.avatar_url"]);

            $query->orderBy("l.id DESC");

            $list = $query->page($pagination, $this->limit)->asArray()->all();

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                'list'       => $list ? $list : [],
                'pagination' => $pagination,
            ]);
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }

    public function statistics(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = ShoppingVoucherLog::find()->alias("l")->where([
                'l.mall_id' => \Yii::$app->mall->id
            ])->innerJoin(["u" => User::tableName()], "l.user_id=u.id");

            if ($this->start_date && $this->end_date) {
                $query->andWhere([
                    "AND",
                    ["<", "l.created_at", strtotime($this->end_date)],
                    [">", "l.created_at", strtotime($this->start_date)]
                ]);
            }

            if ($this->source_type) {
                $query->andWhere(['source_type' => $this->source_type]);
            }

            if ($this->keyword && $this->kw_type) {
                switch ($this->kw_type)
                {
                    case "mobile":
                        $query->andWhere(['u.mobile' => $this->keyword]);
                        break;
                    case "user_id":
                        $query->andWhere(['u.id' => $this->keyword]);
                        break;
                    case "nickname":
                        $query->andWhere(['like', 'u.nickname', $this->keyword]);
                        break;
                    default:
                }
            }

            $query->select(["l.*", "u.nickname", "u.role_type", "u.avatar_url"]);

            $query->orderBy("l.id DESC");

            $incomeQuery = clone $query;
            $income = $incomeQuery->andWhere(['l.type' => 1])->sum('l.money');
            $expendQuery = clone $query;
            $expend = $expendQuery->andWhere(['l.type' => 2])->sum('l.money');

            $list = $query->page($pagination, $this->limit)->asArray()->all();
            $currentIncome = $currentExpend = 0;
            if ($list) {
                foreach ($list as $v) {
                    if ($v['type'] == 1) {
                        $currentIncome += $v['money'];
                    } else {
                        $currentExpend += $v['money'];
                    }
                }
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                'Statistics' => [
                    'income' => $income ?: 0,
                    'expend' => $expend ?: 0,
                    'currentIncome' => sprintf("%.2f", $currentIncome),
                    'currentExpend' => sprintf("%.2f", $currentExpend),
                ],
            ]);
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }
}