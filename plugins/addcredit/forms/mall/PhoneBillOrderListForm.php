<?php

namespace app\plugins\addcredit\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditOrderRefund;
use yii\db\ActiveQuery;

class PhoneBillOrderListForm extends BaseModel
{
    public $status;
    public $keyword;
    public $date_start;
    public $date_end;

    public function rules()
    {
        return [
            [['status', 'keyword', 'date_start', 'date_end'], 'string']
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $query = AddcreditOrder::find()->alias("ao");
            $query->innerJoin(["u" => User::tableName()], "u.id=ao.user_id");
            if (!empty($this->keyword)) {
                $query->andWhere([
                    "OR",
                    ["LIKE", "ao.mobile", $this->keyword],
                    ["LIKE", "ao.order_no", $this->keyword],
                    ["LIKE", "ao.id", $this->keyword],
                ]);
            }
            if (!empty($this->date_start) && !empty($this->date_end)) {
                $query->andFilterWhere(['between', 'ao.pay_at', $this->date_start, $this->date_end]);
            }
            $this->statusFilter($query);
            $query->orderBy("ao.id DESC");
            $selects = [
                "u.id as user_id", "u.nickname", "ao.id", "ao.mobile",
                "ao.order_no", "ao.order_price", "ao.integral_deduction_price", "ao.pay_price",
                "ao.pay_status", "ao.order_status", "ao.recharge_type", "ao.is_manual",
                "from_unixtime( ao.pay_at, '%Y-%m-%d %H:%i:%s' ) AS pay_at ",
                "from_unixtime( ao.created_at, '%Y-%m-%d %H:%i:%s' ) AS created_at ",
                "from_unixtime( ao.updated_at, '%Y-%m-%d %H:%i:%s' ) AS updated_at ",
            ];

            $list = $query->select($selects)->page($pagination, 10)->asArray()->all();
            if ($list) {
               foreach ($list as &$row) {
                   //$row['order_status'] = '';
               }
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list ? $list : [],
                    'pagination' => $pagination
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }

    /**
     * 查询状态
     * @param ActiveQuery $query
     * @return void
     */
    protected function statusFilter(ActiveQuery $query)
    {
        if (!empty($this->status)) {
            $method = "statusFor" . $this->status;
            if (method_exists($this, $method)) {
                $this->$method($query);
            }
        }
    }

    /**
     * 充值成功
     * @param ActiveQuery $query
     * @return void
     */
    private function statusForRechargeSuccess(ActiveQuery $query)
    {
        $query->andWhere([
            'and',
            ["ao.pay_status" => "paid"],
            ["ao.order_status" => "success"],
        ]);
    }

    /**
     * 已退款状态
     * @param ActiveQuery $query
     * @return void
     */
    private function statusForRefunded(ActiveQuery $query)
    {
        $query->andWhere(["ao.pay_status" => "refund"]);
    }

    /**
     * 未付款
     * @param ActiveQuery $query
     * @return void
     */
    private function statusForUnpaid(ActiveQuery $query)
    {
        $query->orWhere([
            "or",
            ["ao.order_status" => "unpaid"],
            ["ao.pay_status" => "unpaid"]
        ]);
    }

    //售后订单列表
    public function getRefundList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $query = AddcreditOrderRefund::find()->alias("aor");
            $query->innerJoin(["ao" => AddcreditOrder::tableName()], "ao.id=aor.order_id");
            $query->innerJoin(["u" => User::tableName()], "u.id=ao.user_id");
            if (!empty($this->keyword)) {
                $query->andWhere([
                    "OR",
                    ["LIKE", "ao.mobile", $this->keyword],
                    ["LIKE", "aor.id", $this->keyword],
                ]);
            }
            $query->orderBy("aor.id DESC");
            $selects = [
                "u.id as user_id", "u.nickname",
                "aor.id", "aor.refund_price","aor.refund_integral","aor.reason",
                "ao.mobile", "ao.order_price", "ao.integral_deduction_price", "ao.pay_price", "ao.pay_status", "ao.order_status",
                "from_unixtime( ao.pay_at, '%Y-%m-%d %H:%i:%s' ) AS pay_at ",
                "from_unixtime( ao.created_at, '%Y-%m-%d %H:%i:%s' ) AS created_at ",
                "from_unixtime( ao.updated_at, '%Y-%m-%d %H:%i:%s' ) AS updated_at ",
            ];

            $list = $query->select($selects)->page($pagination)->asArray()->all();
            if ($list) {
                foreach ($list as &$row) {
                    if ($row['pay_status'] == AddcreditOrder::PAY_TYPE_PAID) {
                        switch ($row['order_status'])
                        {
                            case AddcreditOrder::ORDER_STATUS_FAIL:
                                $row['status'] = '失败';
                                break;
                            case AddcreditOrder::ORDER_STATUS_PRO:
                                $row['status'] = '充值中';
                                break;
                            case AddcreditOrder::ORDER_STATUS_SUC:
                                $row['status'] = '充值成功';
                                break;
                            case AddcreditOrder::ORDER_STATUS_UNP:
                                $row['status'] = '未付款';
                                break;
                            default:
                                $row['status'] = '未知';
                                break;
                        }
                    } elseif ($row['pay_status'] == AddcreditOrder::PAY_TYPE_REFUND) {
                        $row['status'] = '已退款';
                    } elseif ($row['pay_status'] == AddcreditOrder::PAY_TYPE_UNP) {
                        $row['status'] = '未支付';
                    } elseif ($row['pay_status'] == AddcreditOrder::PAY_TYPE_REF) {
                        $row['status'] = '退款中';
                    }
                }
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'pagination' => $pagination
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }
}