<?php

namespace app\plugins\addcredit\forms\api\order;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditPlateforms;

class RechargeRecordForm extends BaseModel
{
    public $plateforms_id;
    public $page;
    public $recharge_time;

    public function rules()
    {
        return [
            [['plateforms_id'], 'required'],
            [['page'], 'integer'],
            [['recharge_time'], 'string'],
        ];
    }

    public function RechargeList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $query = AddcreditOrder::find();
            $query->andWhere([
                'plateform_id' => $this->plateforms_id,
                'user_id'    => \Yii::$app->user->id,
                'mall_id'    => \Yii::$app->mall->id
            ])
                ->select([
                    "id",
                    "mobile",
                    "order_price",
                    "pay_status",
                    "order_status",
                    "DATE_FORMAT(FROM_UNIXTIME(created_at),'%Y-%m-%d %H:%i:%s') as created_at",
                ]);
            if ($this->recharge_time) {
                $query->andWhere('FROM_UNIXTIME(created_at,"%Y-%m-%d")="' . $this->recharge_time . '"');
            }

            $result = $query->orderBy('created_at DESC')->page($pagination, 10)->asArray()->all();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $result,
                'money_list' => $this->rechargeMoneyList($this->plateforms_id),
                'msg' => '',
                'pagination' => $pagination,
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function rechargeMoneyList ($plateforms_id)
    {
        $plateforms = AddcreditPlateforms::find()->where(["is_enabled" => 1])->orderBy("id DESC")->one();
        if (!$plateforms) {
            throw new \Exception('平台不存在！',ApiCode::CODE_FAIL);
        }
        return [
            'FastCharging' => [
                [
                    'redbag_num'   => 50 + 50 * $plateforms->ratio / 100,
                    'price'        => 50,
                    'product_id'   => 1,
                    'plateform_id' => $plateforms->id
                ],
                [
                    'redbag_num'   => 100 + 100 * $plateforms->ratio / 100,
                    'price'        => 100,
                    'product_id'   => 2,
                    'plateform_id' => $plateforms->id
                ],
                [
                    'redbag_num'   => 200 + 200 * $plateforms->ratio / 100,
                    'price'        => 200,
                    'product_id'   => 3,
                    'plateform_id' => $plateforms->id
                ],
            ],
            'SlowCharge' => [
                [
                    'redbag_num'   => 50 + 50 * $plateforms->ratio / 100,
                    'price'        => 50,
                    'product_id'   => 4,
                    'plateform_id' => $plateforms->id
                ],
                [
                    'redbag_num'   => 100 + 100 * $plateforms->ratio / 100,
                    'price'        => 100,
                    'product_id'   => 5,
                    'plateform_id' => $plateforms->id
                ],
                [
                    'redbag_num'   => 200 + 200 * $plateforms->ratio / 100,
                    'price'        => 200,
                    'product_id'   => 6,
                    'plateform_id' => $plateforms->id
                ],
            ],
        ];
    }
}