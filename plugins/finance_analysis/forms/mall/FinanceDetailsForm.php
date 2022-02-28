<?php

namespace app\plugins\finance_analysis\forms\mall;

use app\core\ApiCode;
use app\models\BalanceLog;
use app\models\BaseModel;
use app\models\IncomeLog;
use app\models\IntegralDeduct;
use app\models\IntegralLog;
use app\models\IntegralRecord;
use app\models\OrderDetail;
use app\models\ScoreLog;
use app\plugins\commission\models\CommissionGoodsPriceLog;
use app\plugins\group_buy\models\Order;
use app\plugins\shopping_voucher\models\ShoppingVoucherLog;
use app\plugins\shopping_voucher\models\ShoppingVoucherUser;
use app\plugins\sign_in\models\User;

class FinanceDetailsForm extends BaseModel
{
    public $balance_incomePagination_page;
    public $balance_expenditurePagination_page;
    public $incomePagination_page;
    public $expenditurePagination_page;
    public $RedPacket_incomePagination_page;
    public $RedPacket_expenditurePagination_page;
    public $Integral_incomePagination_page;
    public $Integral_expenditurePagination_page;
    public $Integral_incomeDynamicPagination_page;
    public $Integral_expenditureDynamicPagination_page;
    public $ShoppingVoucher_incomePagination_page;
    public $ShoppingVoucher_expenditurePagination_page;
    public $user_id;
    public $type;
    public $start_time;
    public $end_time;
    public $limit;

    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id'], 'number', 'min' => 1],
            [['user_id', 'balance_incomePagination_page', 'balance_expenditurePagination_page', 'incomePagination_page', 'expenditurePagination_page', 'RedPacket_incomePagination_page', 'RedPacket_expenditurePagination_page', 'Integral_incomePagination_page', 'Integral_expenditurePagination_page', 'Integral_incomeDynamicPagination_page', 'Integral_expenditureDynamicPagination_page', 'ShoppingVoucher_incomePagination_page', 'ShoppingVoucher_expenditurePagination_page', 'limit'], 'integer'],
            [['type', 'start_time', 'end_time'], 'string'],
        ];
    }

    public function get()
    {
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $query = User::find()->alias('u')->where(['u.id' => $this->user_id,'u.is_delete' => 0, 'u.mall_id' => \Yii::$app->mall->id]);

        //获取现有、总金额（不随时间变化）
        $countQuery = clone $query;
        $select = ["u.id", "u.nickname", "u.avatar_url", "u.total_score", "u.score", "u.static_score", "u.balance", "u.total_balance", "u.static_integral", "u.role_type", "u.total_income", "u.income", "u.income_frozen", "u.created_at", "psvu.money"];
        $countUser = $countQuery->select($select)->leftJoin(['psvu' => ShoppingVoucherUser::tableName()], "psvu.user_id=u.id")->asArray()->one();

        //获取某个时间段金额
        $balanceQuery = clone $query;
        $incomeQuery = clone $query;
        $redPacketQuery = clone $query;
        $permanentIntegralQuery = clone $query;
        $dynamicIntegralQuery = clone $query;
        $dynamicDisIntegralQuery = clone $query;
        $ShoppingVoucherQuery = clone $query;

        //获取余额
        $balanceQuery->leftJoin(['b' => BalanceLog::tableName()], "b.user_id=u.id");

        //获取收益
        $incomeQuery->leftJoin(['i' => IncomeLog::tableName()], "i.user_id=u.id");

        //金豆
        $redPacketQuery->leftJoin(['it' => IntegralLog::tableName()], "it.user_id=u.id");

        //永久积分
        $permanentIntegralQuery->leftJoin(['sl' => ScoreLog::tableName()], "sl.user_id=u.id");

        //动态积分(收入)
        $dynamicIntegralQuery->leftJoin(['ir' => IntegralRecord::tableName()], "ir.user_id=u.id");

        //动态积分(支出)
        $dynamicDisIntegralQuery->leftJoin(['id' => IntegralDeduct::tableName()], "id.user_id=u.id");

        //红包
        $ShoppingVoucherQuery->leftJoin(['svl' => ShoppingVoucherLog::tableName()], "svl.user_id=u.id");

        $startTime = strtotime($this->start_time);
        $endTime = strtotime($this->end_time);
        if ($startTime && $endTime) {
            $balanceQuery->andWhere([
                'and',
                ['>=', 'b.created_at', $startTime],
                ['<=', 'b.created_at', $endTime],
            ]);
            $incomeQuery->andWhere([
                'and',
                ['>=', 'i.created_at', $startTime],
                ['<=', 'i.created_at', $endTime],
            ]);
            $redPacketQuery->andWhere([
                'and',
                ['>=', 'it.created_at', $startTime],
                ['<=', 'it.created_at', $endTime],
            ]);
            $permanentIntegralQuery->andWhere([
                'and',
                ['>=', 'sl.created_at', $startTime],
                ['<=', 'sl.created_at', $endTime],
            ]);
            $dynamicIntegralQuery->andWhere([
                'and',
                ['>=', 'ir.created_at', $startTime],
                ['<=', 'ir.created_at', $endTime],
            ]);
            $dynamicDisIntegralQuery->andWhere([
                'and',
                ['>=', 'id.created_at', $startTime],
                ['<=', 'id.created_at', $endTime],
            ]);
            $ShoppingVoucherQuery->andWhere([
                'and',
                ['>=', 'svl.created_at', $startTime],
                ['<=', 'svl.created_at', $endTime],
            ]);
        }
        switch ($this->type)
        {
            case 'balance':
                $balanceType = true;
                break;
            case 'RedPacket':
                $redPacketType = true;
                break;
            case 'Integral':
                $IntegralType = true;
                break;
            case 'ShoppingVoucher':
                $ShoppingVoucherType = true;
                break;
            case 'income':
                $incomeType = true;
                break;
            default:
                $incomeType = true;
        }

        /*-------------------------------余额---------------------------------------*/
        $balance = $this->getBalance($balanceQuery, $balanceType);
        /*-------------------------------收益---------------------------------------*/
        $income = $this->getIncome($incomeQuery, $incomeType);
        /*-------------------------------金豆---------------------------------------*/
        $RedPacket = $this->getRedPacket($redPacketQuery, $redPacketType);
        /*-------------------------------积分---------------------------------------*/
        $Integral = $this->getIntegral($permanentIntegralQuery, $dynamicIntegralQuery, $dynamicDisIntegralQuery, $IntegralType);
        /*-------------------------------红包---------------------------------------*/
        $ShoppingVoucher = $this->getShoppingVoucher($ShoppingVoucherQuery, $ShoppingVoucherType);

        return $this->returnApiResultData(
            ApiCode::CODE_SUCCESS,
            '',
            [
                'countUser' => $countUser,
                'balance' => $balance,
                'income' => $income,
                'RedPacket' => $RedPacket,
                'Integral' => $Integral,
                'ShoppingVoucher' => $ShoppingVoucher
            ]
        );
    }

    protected function getBalance ($balanceQuery, $type)
    {
        $incomePagination = $expenditurePagination = null;

        //获取某个时间段收入余额
        $incomeQuery = clone $balanceQuery;
        $incomeBalance = $incomeQuery->andWhere(['b.type' => 1])->sum('b.money');

        //获取某个时间段支出余额
        $expenditureQuery = clone $balanceQuery;
        $expenditureBalance = $expenditureQuery->andWhere(['b.type' => 2])->sum('b.money');

        if ($type) {
            $balanceSelect = ["b.id", "b.type", "b.money", "b.balance", "b.desc", "b.source_type", "b.created_at"];
            //获取某个时间段收入明细
            $incomeList = $incomeQuery->select($balanceSelect)->page($incomePagination, $this->limit, $this->balance_incomePagination_page)->orderBy("b.id DESC")->asArray()->all();
            //获取某个时间段支出明细
            $expenditureList = $expenditureQuery->select($balanceSelect)->page($expenditurePagination, $this->limit, $this->balance_expenditurePagination_page)->orderBy("b.id DESC")->asArray()->all();
        }
        return [
            'incomeBalance' => $incomeBalance ?: 0,
            'expenditureBalance' => $expenditureBalance ?: 0,
            'incomeList' => $incomeList ?? [],
            'expenditureList' => $expenditureList ?? [],
            'incomePagination' => $incomePagination,
            'expenditurePagination' => $expenditurePagination,
        ];

    }

    protected function getIncome ($incomeQuery, $type)
    {
        $incomePagination = $expenditurePagination = null;
        //获取某个时间段冻结余额
        $frozenQuery = clone $incomeQuery;
        $frozenBalance = $frozenQuery->andWhere(['i.flag' => 0])->sum('i.income');

        //获取某个时间段结算余额
        $settlementQuery = clone $incomeQuery;
        $settlementBalance = $settlementQuery->andWhere(['i.flag' => 1])->sum('i.income');

        //获取某个时间段收入余额
        $inQuery = clone $incomeQuery;
        $incomeBalance = $inQuery->andWhere(['i.type' => 1])->sum('i.income');

        //获取某个时间段支出余额
        $expenditureQuery = clone $incomeQuery;
        $expenditureBalance = $expenditureQuery->andWhere(['i.type' => 2])->sum('i.income');

        if ($type) {
            $incomeSelect = ["i.id", "i.type", "i.money", "i.income", "i.desc", "i.source_type", "i.created_at", "i.source_id"];
            //获取某个时间段收入明细
            $incomeList = $inQuery->select($incomeSelect)->page($incomePagination, $this->limit, $this->incomePagination_page)->orderBy("i.id DESC")->asArray()->all();
            if ($incomeList) {
                foreach ($incomeList as &$item) {
                    if ($item['source_type'] == 'goods') {
                        $goods_commission = CommissionGoodsPriceLog::findOne($item['source_id']);
                        $order = \app\models\Order::findOne($goods_commission->order_id);
                        $item['order_no'] = $order ? $order->order_no : '';
                    } else {
                        $item['order_no'] = '';
                    }
                }
            }
            //获取某个时间段支出明细
            $expenditureList = $expenditureQuery->select($incomeSelect)->page($expenditurePagination, $this->limit, $this->expenditurePagination_page)->orderBy("i.id DESC")->asArray()->all();
            if ($expenditureList) {
                foreach ($expenditureList as &$value) {
                    if ($value['source_type'] == 'goods') {
                        $goods_commission = CommissionGoodsPriceLog::findOne($value['source_id']);
                        $order = \app\models\Order::findOne($goods_commission->order_id);
                        $value['order_no'] = $order ? $order->order_no : '';
                    } else {
                        $value['order_no'] = '';
                    }
                }
            }

        }
        return [
            'frozenBalance' => $frozenBalance ?: 0,
            'settlementBalance' => $settlementBalance ?: 0,
            'incomeBalance' => $incomeBalance ?: 0,
            'expenditureBalance' => $expenditureBalance ?: 0,
            'incomeList' => $incomeList ?? [],
            'expenditureList' => $expenditureList ?? [],
            'incomePagination' => $incomePagination,
            'expenditurePagination' => $expenditurePagination,
        ];

    }

    protected function getRedPacket ($redPacketQuery, $type)
    {
        $incomePagination = $expenditurePagination = null;
        //获取某个时间段收入金豆
        $incomeQuery = clone $redPacketQuery;
        $incomeRedPacket = $incomeQuery->andWhere(['it.type' => 1])->sum('it.integral');

        //获取某个时间段支出金豆
        $expenditureQuery = clone $redPacketQuery;
        $expenditureRedPacket = $expenditureQuery->andWhere(['it.type' => 2])->sum('it.integral');

        if ($type) {
            $redPacketSelect = ["it.id", "it.type", "it.integral", "it.current_integral", "it.desc", "it.source_type", "it.created_at"];
            //获取某个时间段收入明细
            $incomeList = $incomeQuery->select($redPacketSelect)->page($incomePagination, $this->limit, $this->RedPacket_incomePagination_page)->orderBy("it.id DESC")->asArray()->all();
            //获取某个时间段支出明细
            $expenditureList = $expenditureQuery->select($redPacketSelect)->page($expenditurePagination, $this->limit, $this->RedPacket_expenditurePagination_page)->orderBy("it.id DESC")->asArray()->all();
        }
        return [
            'incomeRedPacket' => $incomeRedPacket ?: 0,
            'expenditureRedPacket' => $expenditureRedPacket ?: 0,
            'incomeList' => $incomeList ?? [],
            'expenditureList' => $expenditureList ?? [],
            'incomePagination' => $incomePagination,
            'expenditurePagination' => $expenditurePagination,
        ];

    }

    protected function getIntegral ($permanentIntegralQuery, $dynamicIntegralQuery, $dynamicDisIntegralQuery, $type)
    {
        $incomePermanentPagination = $expenditurePermanentPagination = null;
        $incomeDynamicPagination = $expenditureDynamicPagination = null;
        //获取某个时间段收入永久积分
        $incomePermanentQuery = clone $permanentIntegralQuery;
        $incomePermanentIntegral = $incomePermanentQuery->andWhere(['sl.type' => 1])->sum('sl.score');

        //获取某个时间段支出永久积分
        $expenditurePermanentQuery = clone $permanentIntegralQuery;
        $expenditurePermanentIntegral = $expenditurePermanentQuery->andWhere(['sl.type' => 2])->sum('sl.score');

        //获取某个时间段收入动态积分
        $incomeDynamicQuery = clone $dynamicIntegralQuery;
        $incomeDynamicIntegral = $incomeDynamicQuery->andWhere(['ir.type' => 2, "ir.controller_type" => 0])->sum('ir.money');

        //获取某个时间段支出动态积分
        $expenditureDynamicDisQuery = clone $dynamicDisIntegralQuery;
        $expenditureDynamicDisIntegral = $expenditureDynamicDisQuery->andWhere(['and', ['id.controller_type' => 0], ['<', 'id.money', 0]])->sum('id.money');
        if ($type) {
            $permanentIntegralSelect = ["sl.id", "sl.type", "sl.score", "sl.current_score", "sl.desc", "sl.source_type", "sl.created_at"];
            //获取某个时间段收入永久明细
            $incomeList = $incomePermanentQuery->select($permanentIntegralSelect)->page($incomePermanentPagination, $this->limit, $this->Integral_incomePagination_page)->orderBy("sl.id DESC")->asArray()->all();
            //获取某个时间段支出永久明细
            $expenditureList = $expenditurePermanentQuery->select($permanentIntegralSelect)->page($expenditurePermanentPagination, $this->limit, $this->Integral_expenditurePagination_page)->orderBy("sl.id DESC")->asArray()->all();

            $incomeIntegralSelect = ["ir.id", "ir.type", "ir.controller_type", "ir.money", "ir.before_money", "ir.desc", "ir.created_at"];
            $expenditureIntegralSelect = ["id.id", "id.controller_type", "id.money", "id.desc", "id.before_money", "id.created_at"];
            //获取某个时间段收入动态明细
            $incomeDynamicList = $incomeDynamicQuery->select($incomeIntegralSelect)->orderBy("ir.id DESC")->page($incomeDynamicPagination, $this->limit, $this->Integral_incomeDynamicPagination_page)->asArray()->all();
            //获取某个时间段支出动态明细
            $expenditureDynamicList = $expenditureDynamicDisQuery->select($expenditureIntegralSelect)->page($expenditureDynamicPagination, $this->limit, $this->Integral_expenditureDynamicPagination_page)->orderBy("id.id DESC")->asArray()->all();
        }
        return [
            'incomePermanentIntegral' => $incomePermanentIntegral ?: 0,
            'expenditurePermanentIntegral' => $expenditurePermanentIntegral ?: 0,
            'incomeDynamicIntegral' => $incomeDynamicIntegral ?: 0,
            'expenditureDynamicDisIntegral' => abs($expenditureDynamicDisIntegral),
            'incomeList' => $incomeList ?? [],
            'expenditureList' => $expenditureList ?? [],
            'incomeDynamicList' => $incomeDynamicList ?? [],
            'expenditureDynamicList' => $expenditureDynamicList ?? [],
            'incomePagination' => $incomePermanentPagination,
            'expenditurePagination' => $expenditurePermanentPagination,
            'incomeDynamicPagination' => $incomeDynamicPagination,
            'expenditureDynamicPagination' => $expenditureDynamicPagination,
        ];

    }

    protected function getShoppingVoucher ($ShoppingVoucherQuery, $type)
    {
        $incomePagination = $expenditurePagination = null;
        //获取某个时间段收入红包
        $incomeQuery = clone $ShoppingVoucherQuery;
        $incomeShoppingVoucher = $incomeQuery->andWhere(['svl.type' => 1])->sum('svl.money');

        //获取某个时间段支出红包
        $expenditureQuery = clone $ShoppingVoucherQuery;
        $expenditureShoppingVoucher = $expenditureQuery->andWhere(['svl.type' => 2])->sum('svl.money');
        if ($type) {
            $ShoppingVoucherSelect = ["svl.id", "svl.type", "svl.current_money", "svl.money", "svl.desc", "svl.source_type", "svl.created_at"];
            //获取某个时间段收入明细
            $incomeList = $incomeQuery->select($ShoppingVoucherSelect)->page($incomePagination, $this->limit, $this->ShoppingVoucher_incomePagination_page)->orderBy("svl.id DESC")->asArray()->all();
            //获取某个时间段支出明细
            $expenditureList = $expenditureQuery->select($ShoppingVoucherSelect)->page($expenditurePagination, $this->limit, $this->ShoppingVoucher_expenditurePagination_page)->orderBy("svl.id DESC")->asArray()->all();
        }
        return [
            'incomeShoppingVoucher' => $incomeShoppingVoucher ?: 0,
            'expenditureShoppingVoucher' => $expenditureShoppingVoucher ?: 0,
            'incomeList' => $incomeList ?? [],
            'expenditureList' => $expenditureList ?? [],
            'incomePagination' => $incomePagination,
            'expenditurePagination' => $expenditurePagination,
        ];
    }
}