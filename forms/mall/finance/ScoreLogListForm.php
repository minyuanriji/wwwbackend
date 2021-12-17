<?php

namespace app\forms\mall\finance;

use app\core\ApiCode;
use app\forms\mall\export\ScoreLogExport;
use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\models\ScoreLog;
use app\models\User;

class ScoreLogListForm extends BaseModel
{
    public $page;
    public $limit;
    public $start_date;
    public $end_date;
    public $keyword;
    public $kw_type;
    public $user_id;
    public $source_type;
    public $flag;
    public $fields;

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

        $query = ScoreLog::find()->alias('b')->where([
            'b.mall_id' => \Yii::$app->mall->id,
        ])->innerJoin(["u" => User::tableName()], "u.id=b.user_id")
          ->andWhere(['and', ['!=', 'u.mobile', ''], ['IS NOT', 'u.mobile', NULL], ['u.is_delete' => 0]])
          ->select(["b.*", "u.nickname", "u.mobile"]);

        if ($this->keyword && $this->kw_type) {
            switch ($this->kw_type) {
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

        if ($this->user_id) {
            $query->andWhere(['b.user_id' => $this->user_id]);
        }

        if ($this->start_date && $this->end_date) {
            $query->andWhere(['<', 'b.created_at', strtotime($this->end_date)])
                ->andWhere(['>', 'b.created_at', strtotime($this->start_date)]);
        }

        if ($this->source_type) {
            switch ($this->source_type)
            {
                case 'order':
                    $query->andWhere(['and', ['b.source_type' => 'normal'], ['like', 'b.desc', '下单积分抵扣']]);
                    break;
                case 'order_cancellation':
                    $query->andWhere(['and', ['b.source_type' => 'normal'], ['like', 'b.desc', '商品订单取消']]);
                    break;
                case 'sign_in':
                    $query->andWhere(['and', ['b.source_type' => 'normal'], ['like', 'b.desc', '签到赠送积分']]);
                    break;
                case 'admin':
                    $query->andWhere(['and', ['b.source_type' => 'normal'], ['like', 'b.desc', '管理员']]);
                    break;
                case 'give':
                    $query->andWhere(['and', ['b.source_type' => 'normal'], ['like', 'b.desc', '订单购买赠送']]);
                    break;
                default:
                    $query->andWhere(['b.source_type' => $this->source_type]);
            }
        }

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new ScoreLogExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query, 'b.');
            return false;
        }

        $list = $query->orderBy('b.id desc')->page($pagination, $this->limit)->asArray()->all();

        if ($list) {
            foreach ($list as &$v) {
                if ($v['source_type'] != 'giftpacks_order') {
                    $v['info_desc'] = $v['custom_desc'] ? SerializeHelper::decode($v['custom_desc']) : [];
                }
            }
            unset($v);
        }

        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
            'list' => $list,
            'export_list' => (new ScoreLogExport())->fieldsList(),
            'pagination' => $pagination
        ]);
    }

    public function statistics()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $query = ScoreLog::find()->alias('b')->where([
            'b.mall_id' => \Yii::$app->mall->id,
        ])->innerJoin(["u" => User::tableName()], "u.id=b.user_id")
            ->andWhere(['and', ['!=', 'u.mobile', ''], ['IS NOT', 'u.mobile', NULL], ['u.is_delete' => 0]]);

        if (!empty($this->keyword)) {
            $query->andWhere([
                "OR",
                ['u.mobile' => $this->keyword],
                ['LIKE', 'u.nickname', $this->keyword]
            ]);
        }

        if ($this->user_id) {
            $query->andWhere(['b.user_id' => $this->user_id]);
        }

        if ($this->start_date && $this->end_date) {
            $query->andWhere(['<', 'b.created_at', strtotime($this->end_date)])
                ->andWhere(['>', 'b.created_at', strtotime($this->start_date)]);
        }

        if ($this->source_type) {
            switch ($this->source_type)
            {
                case 'order':
                    $query->andWhere(['and', ['b.source_type' => 'normal'], ['like', 'b.desc', '下单积分抵扣']]);
                    break;
                case 'order_cancellation':
                    $query->andWhere(['and', ['b.source_type' => 'normal'], ['like', 'b.desc', '商品订单取消']]);
                    break;
                case 'sign_in':
                    $query->andWhere(['and', ['b.source_type' => 'normal'], ['like', 'b.desc', '签到赠送积分']]);
                    break;
                case 'admin':
                    $query->andWhere(['and', ['b.source_type' => 'normal'], ['like', 'b.desc', '管理员']]);
                    break;
                case 'give':
                    $query->andWhere(['and', ['b.source_type' => 'normal'], ['like', 'b.desc', '订单购买赠送']]);
                    break;
                default:
                    $query->andWhere(['b.source_type' => $this->source_type]);
            }
        }

        $incomeQuery = clone $query;
        $income = $incomeQuery->andWhere(['b.type' => 1])->sum('b.score');
        $expendQuery = clone $query;
        $expend = $expendQuery->andWhere(['b.type' => 2])->sum('b.score');

        $list = $query->orderBy('b.id desc')->page($pagination, $this->limit)->asArray()->all();

        $currentIncome = $currentExpend = 0;
        if ($list) {
            foreach ($list as $v) {
                if ($v['type'] == 1) {
                    $currentIncome += $v['score'];
                } else {
                    $currentExpend += $v['score'];
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
    }
}