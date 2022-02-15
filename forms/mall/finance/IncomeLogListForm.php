<?php

namespace app\forms\mall\finance;

use app\core\ApiCode;
use app\forms\mall\export\IncomeLogExport;
use app\helpers\CityHelper;
use app\helpers\SerializeHelper;
use app\models\BalanceLog;
use app\models\BaseModel;
use app\models\IncomeLog;
use app\models\Order;
use app\models\Store;
use app\models\User;
use app\plugins\commission\models\CommissionCheckoutPriceLog;
use app\plugins\commission\models\CommissionGoodsPriceLog;
use app\plugins\mch\models\MchCheckoutOrder;

class IncomeLogListForm extends BaseModel
{
    public $page;
    public $limit;
    public $start_date;
    public $end_date;
    public $keyword;
    public $kw_type;
    public $type;
    public $user_id;
    public $level;
    public $address;
    public $flag;
    public $fields;

    public function rules()
    {
        return [
            [['page', 'limit', 'user_id', 'level'], 'integer'],
            [['keyword', 'start_date', 'end_date', 'type', 'flag', 'kw_type'], 'trim'],
            [['address', 'fields'], 'safe'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $currentIncome = 0;
        $currentExpend = 0;
        $query = IncomeLog::find()->alias('b')->where([
            'b.mall_id' => \Yii::$app->mall->id,
        ])->innerJoin(['u' => User::tableName()], 'u.id=b.user_id')
            ->andWhere(['and', ['!=', 'u.mobile', ''], ['IS NOT', 'u.mobile', NULL], ['u.is_delete' => 0]]);

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
                case "remark":
                    $query->andWhere(['like', 'b.desc', $this->keyword]);
                    break;
                default:
            }
        }
        if ($this->user_id) {
            $query->andWhere(['b.user_id' => $this->user_id]);
        }
        if ($this->type) {
            $query->andWhere(['b.source_type' => $this->type]);
        }
        if ($this->start_date && $this->end_date) {
            $query->andWhere(['<', 'b.created_at', strtotime($this->end_date)])
                ->andWhere(['>', 'b.created_at', strtotime($this->start_date)]);
        }
        if ($this->level && $this->address) {
            if (is_string($this->address)) {
                $this->address = explode(',', $this->address);
            }
            $address = '';
            $where = [];
            if ($this->level == 1) {
                $CityHelper = CityHelper::reverseData(0, 0,$this->address[0]);
                $address = $CityHelper['province']['name'];
                $where = ['s.province_id' => $this->address[0]];
            } elseif ($this->level == 2) {
                $CityHelper = CityHelper::reverseData(0, $this->address[1], $this->address[0]);
                $address = $CityHelper['province']['name'] . " " . $CityHelper['city']['name'];
                $where = ['s.province_id' => $this->address[0], 's.city_id' => $this->address[1]];
            } elseif ($this->level == 3) {
                $CityHelper = CityHelper::reverseData($this->address[2], $this->address[1], $this->address[0]);
                $address = $CityHelper['province']['name'] . " " . $CityHelper['city']['name'] . " " . $CityHelper['district']['name'];
                $where = ['s.province_id' => $this->address[0], 's.city_id' => $this->address[1], 's.district_id' => $this->address[2]];
            }
            if ($this->type == 'goods' && $address) {
                $query->leftJoin(['cgp' => CommissionGoodsPriceLog::tableName()], 'b.source_id=cgp.id')
                    ->leftJoin(['o' => Order::tableName()], 'o.id=cgp.order_id')
                    ->andWhere(['and', ['like', 'o.address', $address]]);
            } elseif ($this->type == 'checkout' && $where) {
                $query->leftJoin(['ccp' => CommissionCheckoutPriceLog::tableName()], 'ccp.id=b.source_id')
                    ->leftJoin(['mc' => MchCheckoutOrder::tableName()], 'mc.id=ccp.checkout_order_id')
                    ->leftJoin(['s' => Store::tableName()], "mc.mch_id=s.mch_id")
                    ->andWhere($where);
            }
        }
        $query->select(['b.*','u.id as uid','u.nickname','u.mobile']);
        $incomeQuery = clone $query;
        $income = $incomeQuery->andWhere(['b.type' => 1])->sum('b.income');
        $expendQuery = clone $query;
        $expend = $expendQuery->andWhere(['b.type' => 2])->sum('b.income');
        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new IncomeLogExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query, 'b.');
            return false;
        }
        $list = $query->page($pagination, $this->limit)->orderBy('b.id desc')->asArray()->all();
        if ($list) {
            foreach ($list as $item) {
                if ($item['type'] == 1) {
                    $currentIncome += $item['income'];
                } else {
                    $currentExpend += $item['income'];
                }
            }
        }
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
            'list' => $list,
            'export_list' => (new IncomeLogExport())->fieldsList(),
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