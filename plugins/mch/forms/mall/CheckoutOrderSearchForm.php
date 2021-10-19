<?php


namespace app\plugins\mch\forms\mall;

use app\core\ApiCode;
use app\helpers\CityHelper;
use app\models\Order;
use app\models\Store;
use app\models\User;
use app\plugins\mch\models\MchCheckoutOrder;
use app\plugins\sign_in\forms\BaseModel;

class CheckoutOrderSearchForm extends BaseModel
{

    const limit = 10;

    public $page;
    public $keyword;
    public $keyword_1;
    public $pay_status;
    public $start_date;
    public $end_date;
    public $pay_mode;
    public $level;
    public $address;

    public function rules()
    {
        return [
            [["page", 'keyword_1', 'level'], "integer"],
            [["keyword", "pay_status", 'start_date', 'end_date', 'pay_mode'], "string"],
            [["address"], "safe"],
        ];
    }

    /**
     * 搜索
     * @return array|bool
     */
    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorMsg();
        }

        try {

            $query = MchCheckoutOrder::find()->alias('mco')
                    ->leftJoin(["s" => Store::tableName()], "s.mch_id=mco.mch_id")
                    ->innerJoin(['u' => User::tableName()], 'u.id=mco.pay_user_id')
                    ->andWhere(['and', ['<>', 'u.mobile', ''], ['IS NOT', 'u.mobile', NULL], ['u.is_delete' => 0]]);

            //支付状态
            if (!empty($this->pay_status)) {
                if ($this->pay_status == "paid") {
                    $query->andWhere(["mco.is_pay" => 1]);
                }
                if ($this->pay_status == "unpaid") {
                    $query->andWhere(["mco.is_pay" => 0]);
                }
            }

            //支付方式
            if (!empty($this->pay_mode)) {
                if ($this->pay_mode == "red_packet") {
                    $query->andWhere("mco.integral_deduction_price > 0");
                }
                if ($this->pay_mode == "balance") {
                    $query->andWhere("mco.pay_price > 0");
                }
            }

            //支付时间
            if ($this->start_date && $this->end_date) {
                $query->andWhere(['<', 'mco.pay_at', strtotime($this->end_date)])
                    ->andWhere(['>', 'mco.pay_at', strtotime($this->start_date)]);
            }

            //关键词搜索
            $query->keyword($this->keyword_1 == 1, ['like', 'u.nickname', $this->keyword]);
            $query->keyword($this->keyword_1 == 2, ['like', 's.name', $this->keyword]);
            $query->keyword($this->keyword_1 == 3, ['mco.order_no' => $this->keyword]);

            //区域搜索
            if ($this->level && $this->address) {
                if (is_string($this->address)) {
                    $this->address = explode(',', $this->address);
                }
                $regionWhere = [];
                if ($this->level == 1) {
                    $regionWhere = ['s.province_id' => $this->address[0]];
                } elseif ($this->level == 2) {
                    $regionWhere = ['s.province_id' => $this->address[0], 's.city_id' => $this->address[1]];
                } elseif ($this->level == 3) {
                    $regionWhere = ['s.province_id' => $this->address[0], 's.city_id' => $this->address[1], 's.district_id' => $this->address[2]];
                }
                $query->andWhere($regionWhere);
            }

            $incomeQuery = clone $query;
            $income = $incomeQuery->sum('mco.order_price');

            $query->select(["mco.*", 'u.nickname', 's.cover_url', 's.name']);

            $rows = $query->page($pagination, self::limit)->orderBy("mco.id DESC")->asArray()->all();

            $list = [];
            $currentIncome = 0;
            if ($rows) {
                foreach ($rows as $row) {
                    $row['format_pay_time'] = date("Y-m-d H:i:s", $row['pay_at']);
                    $currentIncome += $row['order_price'];
                    $list[] = $row;
                }
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                'pagination' => $pagination,
                'list' => $list,
                'Statistics' => [
                    'income' => $income ?: 0,
                    'currentIncome' => sprintf("%.2f", $currentIncome),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }

    }
}