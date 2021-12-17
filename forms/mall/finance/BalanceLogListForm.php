<?php

namespace app\forms\mall\finance;

use app\core\ApiCode;
use app\helpers\CityHelper;
use app\helpers\SerializeHelper;
use app\models\BalanceLog;
use app\models\BaseModel;
use app\models\Order;
use app\models\Store;
use app\models\User;
use app\plugins\mch\models\MchCheckoutOrder;
use app\forms\mall\export\BalanceLogExport;

class BalanceLogListForm extends BaseModel
{
    public $page;
    public $limit;
    public $start_date;
    public $end_date;
    public $keyword;
    public $user_id;
    public $type;
    public $level;
    public $address;
    public $flag;
    public $fields;
    public $kw_type;

    public function rules()
    {
        return [
            [['page', 'limit', 'user_id', 'level'], 'integer'],
            [['type'], 'string'],
            [['keyword', 'start_date', 'end_date', 'flag', 'kw_type'], 'trim'],
            [['address', 'fields'], 'safe'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $currentIncome = 0;
            $currentExpend = 0;
            $query = BalanceLog::find()->alias('b')->where([
                'b.mall_id' => \Yii::$app->mall->id,
            ])->innerJoin(['u' => User::tableName()], 'u.id=b.user_id')
                ->andWhere(['and', ['!=', 'u.mobile', ''], ['IS NOT', 'u.mobile', NULL], ['u.is_delete' => 0]]);

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
            if ($this->type) {
                $query->andWhere(['b.source_type' => $this->type]);
            }
            if ($this->level && $this->address) {
                if (is_string($this->address)) {
                    $this->address = explode(',', $this->address);
                }
                $address = '';
                $where = [];
                if ($this->level == 1) {
                    $CityHelper = CityHelper::reverseData(0, 0, $this->address[0]);
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
                if ($this->type == 'order' && $address) {
                    $query->leftJoin(['o' => Order::tableName()], 'o.id=b.source_id')
                        ->andWhere(['and', ['like', 'o.address', $address]]);
                } elseif ($this->type == 'mch_checkout_order' && $where) {
                    $query->leftJoin(['mc' => MchCheckoutOrder::tableName()], 'mc.id=b.source_id')
                        ->leftJoin(['s' => Store::tableName()], "mc.mch_id=s.mch_id")
                        ->andWhere($where);
                }
            }
            $incomeQuery = clone $query;
            $income = $incomeQuery->andWhere(['b.type' => 1])->sum('b.money');
            $expendQuery = clone $query;
            $expend = $expendQuery->andWhere(['b.type' => 2])->sum('b.money');
            if ($this->flag == "EXPORT") {
                $new_query = clone $query;
                $exp = new BalanceLogExport();
                $exp->fieldsKeyList = $this->fields;
                $exp->export($new_query, 'b.');
                return false;
            }
            $list = $query->select(['b.*', 'u.id as uid', 'u.nickname'])->page($pagination, $this->limit)->orderBy('b.id desc')->asArray()->all();
            foreach ($list as &$v) {
                if (!empty($v['custom_desc'])) {
                    $v['info_desc'] = SerializeHelper::decode($v['custom_desc']);
                } else {
                    $v['info_desc'] = [];
                }
                if ($v['type'] == 1) {
                    $currentIncome += $v['money'];
                } else {
                    $currentExpend += $v['money'];
                }
            }
            unset($v);
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                'list' => $list,
                'export_list' => (new BalanceLogExport())->fieldsList(),
                'Statistics' => [
                    'income' => $income ?: 0,
                    'expend' => $expend ?: 0,
                    'currentIncome' => sprintf("%.2f", $currentIncome),
                    'currentExpend' => sprintf("%.2f", $currentExpend),
                ],
                'pagination' => $pagination
            ]);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }
}