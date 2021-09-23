<?php

namespace app\forms\mall\finance;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\IntegralLog;
use app\models\User;

class IntegralLogListForm extends BaseModel
{
    public $page;
    public $limit;
    public $start_date;
    public $end_date;
    public $keyword;
    public $user_id;
    public $is_manual;
    public $source_type;

    public function rules()
    {
        return [
            [['page', 'limit', 'user_id'], 'integer'],
            [['keyword', 'start_date', 'end_date', 'source_type'], 'trim'],
            [['is_manual',], 'safe'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $currentIncome = 0;
        $currentExpend = 0;
        $query = IntegralLog::find()->alias('il')->where([
            'il.mall_id' => \Yii::$app->mall->id,
        ])->orderBy('il.id desc');
        $query->innerJoin(["u" => User::tableName()], "u.id=il.user_id")
            ->andWhere(['and', ['<>', 'u.mobile', ''], ['IS NOT', 'u.mobile', NULL], ['u.is_delete' => 0]]);

        if ($this->user_id) {
            $query->andWhere(['il.user_id' => $this->user_id]);
        }

        if (!empty($this->keyword)) {
            $query->andWhere([
                "OR",
                "u.nickname LIKE '%" . $this->keyword . "%'"
            ]);
        }

        if ($this->is_manual != '') {
            $query->andWhere(['il.is_manual' => $this->is_manual]);
        }

        if ($this->start_date && $this->end_date) {
            $query->andWhere(['<', 'il.created_at', strtotime($this->end_date)])
                ->andWhere(['>', 'il.created_at', strtotime($this->start_date)]);
        }

        $selects = ["il.*", "u.nickname"];

        $query->select($selects);
        $incomeQuery = clone $query;
        $income = $incomeQuery->andWhere(['il.type' => 1])->sum('il.integral');
        $expendQuery = clone $query;
        $expend = $expendQuery->andWhere(['il.type' => 2])->sum('il.integral');
        $list = $query->page($pagination, $this->limit)->asArray()->all();
        if ($list) {
            foreach ($list as $item) {
                if ($item['type'] == 1) {
                    $currentIncome += $item['integral'];
                } else {
                    $currentExpend += $item['integral'];
                }
            }
        }
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
            'list' => $list,
            'Statistics' => [
                'income' => $income ?: 0,
                'expend' => $expend ?: 0,
                'currentIncome' => $currentIncome,
                'currentExpend' => $currentExpend,
            ],
            'pagination' => $pagination
        ]);
    }
}