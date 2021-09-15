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
                    $IncomeQuery = IncomeLog::find()->where(array_merge($mall_id, ['is_delete' => 0]));
                    $this->mergeWhere($IncomeQuery, $dayStartTime, $dayEndTime, $timeStart);
                    $FrozenQuery = clone $IncomeQuery;
                    //待结算
                    $list['frozen_income'] = $FrozenQuery->andWhere(['flag' => 0, 'type' => 1])->sum('income') ?: 0;
                    $CashQuery = clone $IncomeQuery;
                    //已提现
                    $list['cash_income'] = $CashQuery->andWhere(['type' => 2, 'source_type' => 'cash'])->sum('income') ?: 0;
                    //--- 总收益
                    $countQuery = clone $IncomeQuery;
                    $list['count_income'] = $countQuery->andWhere(['type' => 1])->sum('income') ?: 0;
                    if ($this->second_type == 'ToSettled') { //--- 待结算
                        $list['income_list'] = $FrozenQuery->page($Pagination, $this->limit)->orderBy('id DESC')->asArray()->all();
                    } elseif($this->second_type == 'WithdrawnCash') { //已提现
                        $list['income_list'] = $CashQuery->page($Pagination, $this->limit)->orderBy('id DESC')->asArray()->all();
                    }
                    break;
                //红包
                case 'RedEnvelopes':
                    //总共送出去的红包
                    $list['total_red_envelope'] = $this->getTotalRedEnvelope($dayStartTime, $dayEndTime, $timeStart);
                    //已经使用的红包--商品
                    $orderQuery = \app\models\Order::find()->where(array_merge($mall_id, ['is_pay' => 1, 'is_delete' => 0, 'is_recycle' => 0]))->andWhere(['>','integral_deduction_price',0]);
                    $this->mergeWhere($orderQuery, $dayStartTime, $dayEndTime, $timeStart);
                    $list['order_envelope'] = $orderQuery->sum('integral_deduction_price') ?: 0;
                    //已经使用的红包--商家
                    $MchQuery = MchCheckoutOrder::find()->where(array_merge($mall_id, ['is_pay' => 1]))->andWhere(['>','integral_deduction_price',0]);
                    $this->mergeWhere($MchQuery, $dayStartTime, $dayEndTime, $timeStart);
                    $list['mch_envelope'] = $MchQuery->sum('integral_deduction_price') ?: 0;

                    //已经使用的红包--酒店
                    $HotelQuery = HotelOrder::find()->where(array_merge($mall_id, ['order_status' => 'success', 'pay_status' => 'paid', 'pay_type' => 'integral']))->andWhere(['>','integral_deduction_price',0]);
                    $this->mergeWhere($HotelQuery, $dayStartTime, $dayEndTime, $timeStart);
                    $list['hotel_envelope'] = $HotelQuery->sum('integral_deduction_price') ?: 0;

                    //已经使用的红包--大礼包
                    $GiftQuery = GiftpacksOrder::find()->where(array_merge($mall_id, ['pay_status' => 'paid', 'pay_type' => 'integral']))->andWhere(['>','integral_deduction_price',0]);
                    $this->mergeWhere($GiftQuery, $dayStartTime, $dayEndTime, $timeStart);
                    $list['gift_envelope'] = $GiftQuery->sum('integral_deduction_price') ?: 0;
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
                            $list['envelope_list'] = $orderQuery->page($Pagination, $this->limit)->orderBy('id DESC')->asArray()->all();
                            break;
                        case 'RedEnvelopesMch':
                            $list['envelope_list'] = $MchQuery->page($Pagination, $this->limit)->orderBy('id DESC')->asArray()->all();
                            break;
                        case 'RedEnvelopesHotel':
                            $list['envelope_list'] = $HotelQuery->page($Pagination, $this->limit)->orderBy('id DESC')->asArray()->all();
                            break;
                        case 'RedEnvelopesGiftBag':
                            $list['envelope_list'] = $GiftQuery->page($Pagination, $this->limit)->orderBy('id DESC')->asArray()->all();
                            break;
                        default;
                    }
                    break;
                //商户
                case 'Merchant':
                    //商户  --- 已提现
                    $MchCashQuery = MchCash::find()->where(["status" => 1, "transfer_status" => 1, "is_delete" => 0, "type" => "efps_bank"]);
                    $this->mergeWhere($MchCashQuery, $dayStartTime, $dayEndTime, $timeStart);
                    $list['withdrawn_cash'] = $MchCashQuery->sum('money') ?: 0;
                    //商户  --- 未提现
                    $MchNotQuery = Mch::find()->where(array_merge($mall_id, ['review_status' => 1, 'status' => 1, 'is_delete' => 0]))->andWhere(['>','account_money',0]);
                    $this->mergeWhere($MchNotQuery, $dayStartTime, $dayEndTime, $timeStart);
                    $list['No_cash_withdrawal'] = $MchNotQuery->sum('account_money') ?: 0;

                    if ($this->second_type == 'Withdrawal') { //--- 已提现
                        $list['withdrawal_list'] = $MchCashQuery->page($Pagination, $this->limit)->orderBy('id DESC')->asArray()->all();
                    } elseif($this->second_type == 'NoCashWithdrawal') { //未提现
                        $list['withdrawal_list'] = $MchNotQuery->page($Pagination, $this->limit)->orderBy('account_money DESC')->asArray()->all();
                    }
                    break;
                //管理员充值
                case 'adminRecharge':
                    //管理员操作  --- 红包
                    $IntAdminQuery = IntegralLog::find()->alias('i')
                                ->innerJoin(['u' => User::tableName()], 'u.id=i.user_id')
                                ->andWhere(['and', ['<>', 'u.mobile', ''], ['IS NOT', 'u.mobile', null], ['u.is_delete' => 0], ['i.is_manual' => 1], ['i.type' => 1], ['i.source_type' => 'admin'], ['i.mall_id' => \Yii::$app->mall->id]]);
                    if (!empty($this->date_start) && !empty($this->date_end)) {
                        $IntAdminQuery->andWhere([
                            "AND",
                            [">", "i.created_at", $this->date_start],
                            ["<", "i.created_at", $this->date_end]
                        ]);
                    } else {
                        $IntAdminQuery->andWhere([">", "i.created_at", $timeStart]);
                    }
                    $list['integral'] = $IntAdminQuery->sum('i.integral') ?: 0;

                    //收益
                    $IncomeQuery = IncomeLog::find()->alias('i')
                        ->innerJoin(['u' => User::tableName()], 'u.id=i.user_id')
                        ->andWhere(['and', ['<>', 'u.mobile', ''], ['IS NOT', 'u.mobile', null], ['u.is_delete' => 0], ['i.is_delete' => 0], ['i.source_type' => 'admin'], ['i.is_manual' => 1], ['i.type' => 1], ['i.mall_id' => \Yii::$app->mall->id]]);
                    $this->mergeWhere($IncomeQuery, $dayStartTime, $dayEndTime, $timeStart, 'i');
                    $list['Income'] = $IncomeQuery->sum('i.income') ?: 0;

                    //购物券
                    $shopQuery = ShoppingVoucherLog::find()->alias('s')
                        ->innerJoin(['u' => User::tableName()], 'u.id=s.user_id')
                        ->andWhere(['and', ['<>', 'u.mobile', ''], ['IS NOT', 'u.mobile', null], ['u.is_delete' => 0], ['s.type' => 1], ['s.source_type' => 'admin'], ['s.mall_id' => \Yii::$app->mall->id]]);
                    $this->mergeWhere($shopQuery, $dayStartTime, $dayEndTime, $timeStart, 's');
                    $list['ShoppingVoucher'] = $shopQuery->sum('s.money') ?: 0;

                    if ($this->second_type == 'envelopes') { //--- 红包
                        $list['oper_list'] = $IntAdminQuery->page($Pagination, $this->limit)->orderBy('i.id DESC')->asArray()->all();
                    } elseif($this->second_type == 'NoCashWithdrawal') { //收益
                        $list['oper_list'] = $IncomeQuery->page($Pagination, $this->limit)->orderBy('i.id DESC')->asArray()->all();
                    } elseif ($this->second_type == 'ShoppingVoucher') { //购物券
                        $list['oper_list'] = $shopQuery->page($Pagination, $this->limit)->orderBy('s.id DESC')->asArray()->all();
                    }
                    break;
                default;
            }

            /* 获取红包已送出
             * $orderDetQuery = OrderDetail::find()->alias('od')->where(['o.mall_id' => \Yii::$app->mall->id])
                            ->innerJoin(['o' => \app\models\Order::tableName()], 'od.order_id=o.id')
                            ->innerJoin(['g' => Goods::tableName()], 'g.id=od.goods_id')
                            ->andWhere(['o.is_pay' => 1, 'g.enable_integral' => 1])
                            ->select(['COALESCE(SUM(`od`.`num` * substring_index( substring( substring_index( `g`.`integral_setting`, ",", 1 ), 18 ), "\"", 1 )),0) AS `total_red_envelope`']);
            $this->mergeWhere($orderDetQuery, $dayStartTime, $dayEndTime, $timeStart, 'od');
            $RedEnvelopes['total_red_envelope'] = $orderDetQuery->asArray()->one();*/

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

    public function getTotalRedEnvelope ($dayStartTime, $dayEndTime, $timeStart)
    {
        $query = OrderDetail::find()->select("id,order_id,goods_id,num");
        if(!empty($this->date_start) && !empty($this->date_end)){
            $query->andWhere([
                "AND",
                [">", "updated_at", $dayStartTime],
                ["<", "updated_at", $dayEndTime]
            ]);
        }else{
            $query->andWhere([">", "updated_at", $timeStart]);
        }
        $orderDet = $query->asArray()->all();
        $total_red_envelope = 0;
        if ($orderDet) {
            $order_ids = array_column($orderDet, 'order_id');
            $goods_ids = array_column($orderDet, 'goods_id');
            $order = \app\models\Order::find()->where([
                'and',
                ['mall_id' => \Yii::$app->mall->id],
                ['is_pay' => 1],
                ['in', 'id', $order_ids],
                ['is_delete' => 0],
                ['is_recycle' => 0]
            ])->select('id')->asArray()->all();
            if ($order) {
                $newOrder = array_combine(array_column($order, 'id'), $order);
            }
            $goods = Goods::find()->where([
                'and',
                ['enable_integral' => 1],
                ['in', 'id', $goods_ids]
            ])->select('id,enable_integral,integral_setting')->asArray()->all();
            if ($goods) {
                $newGoods = array_combine(array_column($goods, 'id'), $goods);
            }
            foreach ($orderDet as $key => $item) {
                if (!isset($newOrder[$item['order_id']])) {
                    unset($orderDet[$key]);
                }
                if (!isset($newGoods[$item['goods_id']])) {
                    unset($orderDet[$key]);
                } else {
                    $orderDet[$key]['integral_setting'] = json_decode($newGoods[$item['goods_id']]['integral_setting'],true);
                }
            }
            $order_count = count($orderDet);
            $newOrderDet = array_values($orderDet);
            for ($i = 0; $i < $order_count; $i ++) {
                $total_red_envelope += $newOrderDet[$i]['num'] * $newOrderDet[$i]['integral_setting']['integral_num'];
            }
        }
        return $total_red_envelope;
    }

}