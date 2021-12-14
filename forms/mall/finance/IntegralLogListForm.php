<?php

namespace app\forms\mall\finance;

use app\core\ApiCode;
use app\forms\mall\export\IntegralLogExport;
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
    public $source_type;
    public $flag;
    public $fields;
    public $kw_type;

    public function rules()
    {
        return [
            [['page', 'limit', 'user_id'], 'integer'],
            [['keyword', 'start_date', 'end_date', 'source_type', 'flag', 'kw_type'], 'trim'],
            [['fields'], 'safe'],

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
            ->andWhere(['and', ['!=', 'u.mobile', ''], ['IS NOT', 'u.mobile', NULL], ['u.is_delete' => 0]]);

        if ($this->user_id) {
            $query->andWhere(['il.user_id' => $this->user_id]);
        }

        if (!empty($this->keyword) && !empty($this->kw_type)) {
            switch ($this->kw_type)
            {
                case 'mobile':
                    $query->andWhere(['u.mobile' => $this->keyword]);
                    break;
                case 'user_id':
                    $query->andWhere(['il.user_id' => $this->keyword]);
                    break;
                case 'nickname':
                    $query->andWhere([
                        "OR",
                        "u.nickname LIKE '%" . $this->keyword . "%'"
                    ]);
                    break;
                default:
            }
        }

        if ($this->source_type) {
            switch ($this->source_type)
            {
                case 'order':
                    $query->andWhere(['and', ['il.source_type' => 'record'], ['like', 'il.desc', '订单']]);
                    break;
                case 'mch_checkout_order':
                    $query->andWhere(['and', ['il.source_type' => 'record'], ['like', 'il.desc', '商家']]);
                    break;
                default:
                    $query->andWhere(['il.source_type' => $this->source_type]);
            }
        }

        if ($this->start_date && $this->end_date) {
            $query->andWhere(['<', 'il.created_at', strtotime($this->end_date)])
                ->andWhere(['>', 'il.created_at', strtotime($this->start_date)]);
        }

        $query->select(["il.*", "u.nickname", "u.mobile"]);
        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new IntegralLogExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query, 'il.');
            return false;
        }
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
            'export_list' => (new IntegralLogExport())->fieldsList(),
            'Statistics' => [
                'income' => $income ?: 0,
                'expend' => $expend ?: 0,
                'currentIncome' => round($currentIncome, 2),
                'currentExpend' => round($currentExpend, 2),
            ],
            'pagination' => $pagination
        ]);
    }
}