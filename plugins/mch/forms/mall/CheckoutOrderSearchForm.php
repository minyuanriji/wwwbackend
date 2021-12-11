<?php


namespace app\plugins\mch\forms\mall;

use app\core\ApiCode;
use app\helpers\ArrayHelper;
use app\helpers\CityHelper;
use app\models\Order;
use app\models\ScoreLog;
use app\models\Store;
use app\models\User;
use app\plugins\commission\models\CommissionCheckoutPriceLog;
use app\plugins\commission\models\CommissionStorePriceLog;
use app\plugins\integral_card\models\ScoreFromStore;
use app\plugins\integral_card\models\ScoreSendLog;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCheckoutOrder;
use app\plugins\shopping_voucher\models\ShoppingVoucherSendLog;
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
                    ->innerJoin(["m" => Mch::tableName()], "m.id=mco.mch_id")
                    ->innerJoin(["s" => Store::tableName()], "s.mch_id=mco.mch_id")
                    ->innerJoin(['u' => User::tableName()], 'u.id=mco.pay_user_id')
                    ->andWhere(['and', ['!=', 'u.mobile', ''], ['IS NOT', 'u.mobile', NULL], ['u.is_delete' => 0]]);

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

            $query->with('mch.user');

            $query->select(["mco.*", 'u.nickname', 'm.user_id as store_user_id', 'u.avatar_url', 's.cover_url', 's.name', 'm.transfer_rate', 's.id as store_id']);

            $list = $query->page($pagination, self::limit)->orderBy("mco.id DESC")->asArray()->all();

            if ($list) {
                $commissionStorePrice = new CommissionStorePriceLog();
                $commissionCheckoutPrice = new CommissionCheckoutPriceLog();
                foreach ($list as $key => $row) {
                    $list[$key]['format_pay_time'] = date("Y-m-d H:i:s", $row['pay_at']);

                    //获取赠送购物券
                    $shoppingVoucherSend = ShoppingVoucherSendLog::find()->where([
                        'mall_id' => \Yii::$app->mall->id,
                        'user_id' => $row['pay_user_id'],
                        'source_id' => $row['id'],
                        'source_type' => 'from_mch_checkout_order',
                    ])->select('status, money')->one();
                    if ($shoppingVoucherSend) {
                        $list[$key]['send_status'] = $shoppingVoucherSend->status;
                        $list[$key]['send_money'] = $shoppingVoucherSend->money;
                    } else {
                        $list[$key]['send_status'] = '';
                        $list[$key]['send_money'] = 0;
                    }

                    //获取赠送积分
                    $scoreSend = ScoreSendLog::find()->where([
                        'user_id' => $row['pay_user_id'],
                        'source_id' => $row['id'],
                        'source_type' => 'from_mch_checkout_order',
                    ])->select('status')->one();
                    if ($scoreSend) {
                        $scoreFormStore = ScoreFromStore::findOne(['store_id' => $row['store_id']]);
                        if ($scoreFormStore) {
                            $rate = $scoreFormStore->rate;
                        } else {
                            $rate = 0;
                        }
                        $list[$key]['score_status'] = $scoreSend->status;
                        $list[$key]['score_money'] = sprintf("%.2f", $row['pay_price'] * ($rate / 100));
                    } else {
                        $list[$key]['score_status'] = '';
                        $list[$key]['score_money'] = 0;
                    }

                    //获取门店服务费
                    if ($row['transfer_rate'] > 0) {
                        $list[$key]['discount'] = (100 - $row['transfer_rate']) / 10;
                    } else {
                        $list[$key]['discount'] = 0;
                    }

                    //获取直推分佣
                    $directPushCommission = $commissionStorePrice->getDirectPushCommission([
                        'item_id' => $row['id'],
                        'item_type' => 'checkout',
                    ], 'user_id,price,status');
                    if ($directPushCommission) {
                        $list[$key]['direct_push_price'] = $directPushCommission->price;
                        $list[$key]['direct_push_status'] = $directPushCommission->status;
                        $list[$key]['direct_push_user_id'] = $directPushCommission->user->id;
                        $list[$key]['direct_push_user_nickname'] = $directPushCommission->user->nickname;
                        $list[$key]['direct_push_user_avatar_url'] = $directPushCommission->user->avatar_url;
                        $list[$key]['direct_push_user_role_type'] = (new User())::getRoleType($directPushCommission->user->role_type);
                    } else {
                        $list[$key]['direct_push_price'] = 0;
                        $list[$key]['direct_push_status'] = -1;
                        $list[$key]['direct_push_user_id'] = 0;
                        $list[$key]['direct_push_user_nickname'] = '';
                        $list[$key]['direct_push_user_avatar_url'] = '';
                        $list[$key]['direct_push_user_role_type'] = '';
                    }

                    //获取消费分佣
                    $consumptionCommission = $commissionCheckoutPrice->getConsumptionCommission([
                        'checkout_order_id' => $row['id']
                    ], 'user_id,price,status');

                    $list[$key]['consumption'] = $consumptionCommission;

                    if (empty($list[$key]['cover_url']) || $list[$key]['cover_url'] == '/') {
                        $list[$key]['cover_url'] = \Yii::$app->params['store_default_avatar'];
                    }
                }
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                'pagination' => $pagination,
                'list' => $list,
            ]);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }

    }

    //统计
    public function statistics()
    {
        if (!$this->validate()) {
            return $this->responseErrorMsg();
        }

        try {

            $query = MchCheckoutOrder::find()->alias('mco')
                ->leftJoin(["s" => Store::tableName()], "s.mch_id=mco.mch_id")
                ->innerJoin(['u' => User::tableName()], 'u.id=mco.pay_user_id')
                ->andWhere(['and', ['!=', 'u.mobile', ''], ['IS NOT', 'u.mobile', NULL], ['u.is_delete' => 0]]);

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

            $query->select(["mco.order_price"]);

            $rows = $query->page($pagination, self::limit)->orderBy("mco.id DESC")->asArray()->all();

            $list = [];
            $currentIncome = 0;
            if ($rows) {
                foreach ($rows as $row) {
                    $currentIncome += $row['order_price'];
                    $list[] = $row;
                }
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
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