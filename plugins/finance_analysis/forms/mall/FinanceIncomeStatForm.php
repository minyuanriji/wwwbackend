<?php
namespace app\plugins\finance_analysis\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Cash;
use app\models\EfpsPaymentOrder;
use app\models\Goods;
use app\models\IncomeLog;
use app\models\IntegralLog;
use app\models\OrderDetail;
use app\models\Store;
use app\models\User;
use app\plugins\giftpacks\models\GiftpacksGroupPayOrder;
use app\plugins\giftpacks\models\GiftpacksOrder;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCash;
use app\plugins\mch\models\MchCheckoutOrder;
use app\plugins\shopping_voucher\models\ShoppingVoucherLog;

class FinanceIncomeStatForm extends BaseModel
{
    public $date_start;
    public $date_end;
    public $time_start;
    public $type;
    public $second_type;
    public $page;
    public $limit;

    public function rules(){
        return [
            [['page'], 'integer'],
            ['page', 'default', 'value' => 1],
            ['limit', 'default', 'value' => 10],
            [['date_start', 'date_end', 'type', 'second_type'], 'string'],
            [['date_start', 'date_end', 'time_start', 'type', 'second_type'], 'safe']
        ];
    }

    public function get()
    {
        try {
            $timeStart = time() - ($this->time_start ? (int)$this->time_start : 0);
            $dayStartTime = strtotime($this->date_start);
            $dayEndTime = strtotime($this->date_end);
            $mall_id = ['mall_id' => \Yii::$app->mall->id];
            $list = [];

            switch ($this->type)
            {
                //总收益
                case 'TotalRevenue':
                    $IncomeQuery = IncomeLog::find()->alias('i')
                                ->innerJoin(['u' => User::tableName()], 'u.id=i.user_id')
                                ->andWhere(['and', ['<>', 'u.mobile', ''], ['IS NOT', 'u.mobile', null], ['u.is_delete' => 0], ['i.is_delete' => 0], ['i.mall_id' => \Yii::$app->mall->id]])
                                ->select(['i.*', 'u.nickname']);

                    $this->mergeWhere($IncomeQuery, $dayStartTime, $dayEndTime, $timeStart, 'i');
                    $FrozenQuery = clone $IncomeQuery;
                    //待结算
                    $list['frozen_income'] = $FrozenQuery->andWhere(['i.flag' => 0, 'i.type' => 1])->sum('i.income') ?: 0;
                    $CashQuery = clone $IncomeQuery;
                    //已提现
                    $list['cash_income'] = $CashQuery->andWhere(['i.type' => 2, 'i.source_type' => 'cash'])->sum('i.income') ?: 0;
                    //--- 总收益
                    $countQuery = clone $IncomeQuery;
                    $list['count_income'] = $countQuery->andWhere(['i.type' => 1])->sum('i.income') ?: 0;
                    if ($this->second_type == 'ToSettled') { //--- 待结算
                        $list['income_list'] = $FrozenQuery->page($Pagination, $this->limit)->orderBy('i.id DESC')->asArray()->all();
                    } elseif($this->second_type == 'WithdrawnCash') { //已提现
                        $list['income_list'] = $CashQuery->page($Pagination, $this->limit)->orderBy('i.id DESC')->asArray()->all();
                    }
                    break;
                //金豆
                case 'RedEnvelopes':
                    //总共送出去的金豆
                    $totalSendQuery = IntegralLog::find()->alias('i')
                        ->innerJoin(['u' => User::tableName()], 'u.id=i.user_id')
                        ->andWhere(['and', ['<>', 'u.mobile', ''], ['IS NOT', 'u.mobile', null], ['u.is_delete' => 0], ['i.type' => 1], ['i.mall_id' => \Yii::$app->mall->id]]);
                    if (!empty($this->date_start) && !empty($this->date_end)) {
                        $totalSendQuery->andWhere([
                            "AND",
                            [">", "i.created_at", $dayStartTime],
                            ["<", "i.created_at", $dayEndTime]
                        ]);
                    } else {
                        $totalSendQuery->andWhere([">", "i.created_at", $timeStart]);
                    }
                    $list['total_red_envelope'] = $totalSendQuery->sum('i.integral') ?: 0;
                    //已经使用的金豆--商品
                    $orderQuery = \app\models\Order::find()->alias('o')
                                ->innerJoin(['u' => User::tableName()], 'u.id=o.user_id')
                                ->andWhere([
                                    'and',
                                    ['<>', 'u.mobile', ''],
                                    ['IS NOT', 'u.mobile', null],
                                    ['u.is_delete' => 0],
                                    ['o.is_pay' => 1],
                                    ['o.is_delete' => 0],
                                    ['o.is_recycle' => 0],
                                    ['>', 'o.integral_deduction_price' , 0],
                                    ['o.mall_id' => \Yii::$app->mall->id],
                                ])
                                ->select(['o.*', 'u.nickname']);
                    $this->mergeWhere($orderQuery, $dayStartTime, $dayEndTime, $timeStart, 'o');
                    $list['order_envelope'] = $orderQuery->sum('o.integral_deduction_price') ?: 0;
                    //已经使用的金豆--商家
                    $MchQuery = MchCheckoutOrder::find()->alias('mco')
                        ->innerJoin(['u' => User::tableName()], 'u.id=mco.pay_user_id')
                        ->where(['mco.mall_id' => \Yii::$app->mall->id, 'mco.is_pay' => 1])
                        ->andWhere(['and', ['<>', 'u.mobile', ''], ['IS NOT', 'u.mobile', null], ['>', 'mco.integral_deduction_price', 0]])
                        ->select(['mco.*', 'u.nickname']);

                    $this->mergeWhere($MchQuery, $dayStartTime, $dayEndTime, $timeStart, 'mco');
                    $list['mch_envelope'] = $MchQuery->sum('mco.integral_deduction_price') ?: 0;

                    //已经使用的金豆--酒店
                    $HotelQuery = HotelOrder::find()->alias('h')
                        ->innerJoin(['u' => User::tableName()], 'u.id=h.user_id')
                        ->andWhere([
                            'and',
                            ['<>', 'u.mobile', ''],
                            ['IS NOT', 'u.mobile', null],
                            ['u.is_delete' => 0],
                            ['h.order_status' => 'success'],
                            ['h.pay_type' => 'integral'],
                            ['>', 'h.integral_deduction_price', 0],
                            ['h.mall_id' => \Yii::$app->mall->id],
                        ])
                        ->select(['h.*', 'u.nickname']);
                    $this->mergeWhere($HotelQuery, $dayStartTime, $dayEndTime, $timeStart, 'h');
                    $list['hotel_envelope'] = $HotelQuery->sum('h.integral_deduction_price') ?: 0;

                    //已经使用的金豆--大礼包
                    $GiftQuery = GiftpacksOrder::find()->alias('go')
                        ->innerJoin(['u' => User::tableName()], 'u.id=go.user_id')
                        ->where(['go.mall_id' => \Yii::$app->mall->id, 'go.pay_status' => 'paid', 'go.pay_type' => 'integral'])
                        ->andWhere(['and', ['<>', 'u.mobile', ''], ['IS NOT', 'u.mobile', null], ['>', 'go.integral_deduction_price', 0]])
                        ->select(['go.*', 'u.nickname']);
                    $this->mergeWhere($GiftQuery, $dayStartTime, $dayEndTime, $timeStart, 'go');
                    $list['gift_envelope'] = $GiftQuery->sum('go.integral_deduction_price') ?: 0;

                    $GiftGroupQuery = GiftpacksGroupPayOrder::find()->where(array_merge($mall_id, ['pay_status' => 'paid', 'pay_type' => 'integral']))->andWhere(['>','integral_deduction_price',0]);
                    if (!empty($this->date_start) && !empty($this->date_end)) {
                        $GiftGroupQuery->andWhere([
                            "AND",
                            [">", "pay_at", $this->date_start],
                            ["<", "pay_at", $this->date_end]
                        ]);
                    } else {
                        $GiftGroupQuery->andWhere([">", "updated_at", date('Y-m-d H:i:s', $timeStart)]);
                    }
                    $list['gift_group_envelope'] = $GiftGroupQuery->sum('integral_deduction_price') ?: 0;

                    $list['big_gift_envelope'] = $list['gift_envelope'] + $list['gift_group_envelope'];

                    //已使用总数
                    $list['count_envelope'] = round($list['order_envelope'] + $list['mch_envelope'] + $list['hotel_envelope'] + $list['big_gift_envelope'], 2);

                    switch ($this->second_type)
                    {
                        case 'RedEnvelopesGoods':
                            $list['envelope_list'] = $orderQuery->page($Pagination, $this->limit)->orderBy('o.id DESC')->asArray()->all();
                            break;
                        case 'RedEnvelopesMch':
                            $list['envelope_list'] = $MchQuery->page($Pagination, $this->limit)->orderBy('mco.id DESC')->asArray()->all();
                            break;
                        case 'RedEnvelopesHotel':
                            $list['envelope_list'] = $HotelQuery->page($Pagination, $this->limit)->orderBy('h.id DESC')->asArray()->all();
                            break;
                        case 'RedEnvelopesGiftBag':
                            $list['envelope_list'] = $GiftQuery->page($Pagination, $this->limit)->orderBy('go.id DESC')->asArray()->all();
                            break;
                        default;
                    }
                    break;
                //商户
                case 'Merchant':
                    //总收入
                    $mchCheckoutQuery = MchCheckoutOrder::find()->alias('mco')
                        ->innerJoin(['u' => User::tableName()], 'u.id=mco.pay_user_id')
                        ->andWhere(['and', ['<>', 'u.mobile', ''], ['IS NOT', 'u.mobile', null], ['u.is_delete' => 0], ['mco.is_pay' => 1], ['mco.mall_id' => \Yii::$app->mall->id]])
                        ->select(['mco.*', 'u.nickname']);
                    $this->mergeWhere($mchCheckoutQuery, $dayStartTime, $dayEndTime, $timeStart);
                    $list['CheckoutPrice'] = $mchCheckoutQuery->sum('mco.order_price') ?: 0;

                    //商户  --- 已提现
                    $MchCashQuery = MchCash::find()->alias('mc')
                        ->leftJoin(['s' => Store::tableName()], 's.mch_id=mc.mch_id')
                        ->where(["mc.status" => 1, "mc.transfer_status" => 1, "mc.is_delete" => 0, "mc.type" => "efps_bank", "mc.mall_id" => \Yii::$app->mall->id])
                        ->select('mc.*, s.name');
                    $this->mergeWhere($MchCashQuery, $dayStartTime, $dayEndTime, $timeStart, 'mc');
                    $list['withdrawn_cash'] = $MchCashQuery->sum('mc.money') ?: 0;

                    //商户  --- 未提现
                    $MchNotQuery = Mch::find()->alias('m')
                        ->leftJoin(['s' => Store::tableName()], 's.mch_id=m.id')
                        ->where(['m.review_status' => 1, 'm.status' => 1, 'm.is_delete' => 0, "m.mall_id" => \Yii::$app->mall->id])
                        ->andWhere(['>', 'm.account_money', 0])
                        ->select('m.*, s.name');
                    $this->mergeWhere($MchNotQuery, $dayStartTime, $dayEndTime, $timeStart, 'm');
                    $list['No_cash_withdrawal'] = $MchNotQuery->sum('m.account_money') ?: 0;

                    if ($this->second_type == 'Withdrawal') { //--- 已提现
                        $list['withdrawal_list'] = $MchCashQuery->page($Pagination, $this->limit)->orderBy('mc.id DESC')->asArray()->all();
                    } elseif($this->second_type == 'NoCashWithdrawal') { //未提现
                        $list['withdrawal_list'] = $MchNotQuery->page($Pagination, $this->limit)->orderBy('m.account_money DESC')->asArray()->all();
                    } elseif($this->second_type == 'TotalRevenue') { //总收入
                        $list['withdrawal_list'] = $mchCheckoutQuery->page($Pagination, $this->limit)->orderBy('mco.id DESC')->asArray()->all();
                    }
                    break;
                //管理员充值
                case 'adminRecharge':
                    //管理员操作  --- 金豆
                    $IntAdminQuery = IntegralLog::find()->alias('i')
                        ->innerJoin(['u' => User::tableName()], 'u.id=i.user_id')
                        ->andWhere([
                            'and',
                            ['<>', 'u.mobile', ''],
                            ['IS NOT', 'u.mobile', null],
                            ['u.is_delete' => 0],
                            ['i.is_manual' => 1],
                            ['i.type' => 1],
                            ['i.source_type' => 'admin'],
                            ['i.mall_id' => \Yii::$app->mall->id]])
                        ->select('i.*, u.nickname');

                    if (!empty($this->date_start) && !empty($this->date_end)) {
                        $IntAdminQuery->andWhere([
                            "AND",
                            [">", "i.created_at", $dayStartTime],
                            ["<", "i.created_at", $dayEndTime]
                        ]);
                    } else {
                        $IntAdminQuery->andWhere([">", "i.created_at", $timeStart]);
                    }
                    $list['integral'] = $IntAdminQuery->sum('i.integral') ?: 0;

                    //收益
                    $IncomeQuery = IncomeLog::find()->alias('i')
                        ->innerJoin(['u' => User::tableName()], 'u.id=i.user_id')
                        ->andWhere([
                            'and',
                            ['<>', 'u.mobile', ''],
                            ['IS NOT', 'u.mobile', null],
                            ['u.is_delete' => 0],
                            ['i.is_delete' => 0],
                            ['i.source_type' => 'admin'],
                            ['i.is_manual' => 1],
                            ['i.type' => 1],
                            ['i.mall_id' => \Yii::$app->mall->id]])
                        ->select('i.*, u.nickname');
                    $this->mergeWhere($IncomeQuery, $dayStartTime, $dayEndTime, $timeStart, 'i');
                    $list['Income'] = $IncomeQuery->sum('i.income') ?: 0;

                    //红包
                    $shopQuery = ShoppingVoucherLog::find()->alias('s')
                        ->innerJoin(['u' => User::tableName()], 'u.id=s.user_id')
                        ->andWhere([
                            'and',
                            ['<>', 'u.mobile', ''],
                            ['IS NOT', 'u.mobile', null],
                            ['u.is_delete' => 0],
                            ['s.type' => 1],
                            ['s.source_type' => 'admin'],
                            ['s.mall_id' => \Yii::$app->mall->id]])
                        ->select('s.*, u.nickname');
                    $this->mergeWhere($shopQuery, $dayStartTime, $dayEndTime, $timeStart, 's');
                    $list['ShoppingVoucher'] = $shopQuery->sum('s.money') ?: 0;

                    if ($this->second_type == 'envelopes') { //--- 金豆
                        $list['oper_list'] = $IntAdminQuery->page($Pagination, $this->limit)->orderBy('i.id DESC')->asArray()->all();
                    } elseif($this->second_type == 'NoCashWithdrawal') { //收益
                        $list['oper_list'] = $IncomeQuery->page($Pagination, $this->limit)->orderBy('i.id DESC')->asArray()->all();
                    } elseif ($this->second_type == 'ShoppingVoucher') { //红包
                        $list['oper_list'] = $shopQuery->page($Pagination, $this->limit)->orderBy('s.id DESC')->asArray()->all();
                    }
                    break;
                default;
            }

            //收入
            $EfpsQuery = EfpsPaymentOrder::find()->where(["is_pay" => 1]);
            if(!empty($this->date_start) && !empty($this->date_end)){
                $EfpsQuery->andWhere([
                    "AND",
                    [">", "update_at", $dayStartTime],
                    ["<", "update_at", $dayEndTime]
                ]);
            }else{
                $EfpsQuery->andWhere([">", "update_at", $timeStart]);
            }

            $totalIncome = (float)$EfpsQuery->sum("payAmount");
            $totalIncome = round($totalIncome/100, 2);

            //支出包括商户、用户的提现
            $totalDisburse = 0;

            //用户提现
            $query = Cash::find()->where(["type" => "bank", "status" => 2, "is_delete" => 0]);
            $this->mergeWhere($query, $dayStartTime, $dayEndTime, $timeStart);
            $totalDisburse += (float)$query->sum("fact_price") ?: 0;

            //商户提现
            $query = MchCash::find()->where(["status" => 1, "transfer_status" => 1, "is_delete" => 0, "type" => "efps_bank"]);
            $this->mergeWhere($query, $dayStartTime, $dayEndTime, $timeStart);
            $totalDisburse += (float)$query->sum("fact_price") ?: 0;

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '',
                [
                    'business' => [
                        "total_income"   => round($totalIncome, 2),
                        "total_disburse" => round($totalDisburse, 2)
                    ],
                    'list' => $list,
                    'pagination' => $Pagination,
                ]
            );
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, '', [
                'file' => $e->getFile(),
                'Message' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }

    protected function mergeWhere ($query, $dayStartTime, $dayEndTime, $timeStart, $alias='')
    {
        if ($alias) {
            $alias .= '.';
        }
        if(!empty($this->date_start) && !empty($this->date_end)){
            $query->andWhere([
                "AND",
                [">", $alias . "updated_at", $dayStartTime],
                ["<", $alias . "updated_at", $dayEndTime]
            ]);
        } else {
            $query->andWhere([">", $alias . "updated_at", $timeStart]);
        }
    }

    public function getSendEnvelope ($dayStartTime, $dayEndTime, $timeStart)
    {
         //获取金豆已送出
          $orderDetQuery = OrderDetail::find()->alias('od')->where(['o.mall_id' => \Yii::$app->mall->id])
                        ->innerJoin(['o' => \app\models\Order::tableName()], 'od.order_id=o.id')
                        ->innerJoin(['u' => User::tableName()], 'u.id=o.user_id')
                        ->innerJoin(['g' => Goods::tableName()], 'g.id=od.goods_id')
                        ->andWhere(['and', ['<>', 'u.mobile', ''], ['IS NOT', 'u.mobile', null], ['u.is_delete' => 0], ['o.is_pay' => 1], ['g.enable_integral' => 1]])
                        ->select(['COALESCE(SUM(`od`.`num` * substring_index( substring( substring_index( `g`.`integral_setting`, ",", 1 ), 18 ), "\"", 1 )),0) AS `total_red_envelope`']);
        $this->mergeWhere($orderDetQuery, $dayStartTime, $dayEndTime, $timeStart, 'od');
        $total_red_envelope = $orderDetQuery->asArray()->one();
        return $total_red_envelope['total_red_envelope'] ?? 0;
    }

}