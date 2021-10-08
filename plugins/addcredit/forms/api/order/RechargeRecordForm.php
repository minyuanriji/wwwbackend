<?php

namespace app\plugins\addcredit\forms\api\order;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditPlateforms;

class RechargeRecordForm extends BaseModel
{
    public $plateforms_id;

    public function rules()
    {
        return [
            [['plateforms_id'], 'required']
        ];
    }

    public function RechargeList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $query = AddcreditOrder::find();
            $result = $query->andWhere([
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
                ])
                ->orderBy('created_at DESC')->limit(10)->asArray()->all();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $result,
                'money_list' => $this->rechargeMoneyList($this->plateforms_id),
                'msg' => ''
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
        $plateforms = AddcreditPlateforms::findOne($plateforms_id);
        if (!$plateforms) {
            throw new \Exception('平台不存在！',ApiCode::CODE_FAIL);
        }
        return [
            'FastCharging' => [
                /*[
                    'redbag_num' => 10 + 10 * $plateforms->ratio / 100,
                    'price' => 10,
                    'product_id' => 10,
                ],
                [
                    'redbag_num' => 20 + 20 * $plateforms->ratio / 100,
                    'price' => 20,
                    'product_id' => 28,
                ],*/
                [
                    'redbag_num' => 30 + 30 * $plateforms->ratio / 100,
                    'price' => 30,
                    'product_id' => 123,
                ],
                [
                    'redbag_num' => 50 + 50 * $plateforms->ratio / 100,
                    'price' => 50,
                    'product_id' => 124,
                ],
                [
                    'redbag_num' => 100 + 100 * $plateforms->ratio / 100,
                    'price' => 100,
                    'product_id' => 125,
                ],
                [
                    'redbag_num' => 200 + 200 * $plateforms->ratio / 100,
                    'price' => 200,
                    'product_id' => 126,
                ],
            ],
            'SlowCharge' => [
                [
                    'redbag_num' => 30 + 30 * $plateforms->ratio / 100,
                    'price' => 30,
                    'product_id' => 86,
                ],
                [
                    'redbag_num' => 50 + 50 * $plateforms->ratio / 100,
                    'price' => 50,
                    'product_id' => 83,
                ],
                [
                    'redbag_num' => 100 + 100 * $plateforms->ratio / 100,
                    'price' => 100,
                    'product_id' => 84,
                ],
                [
                    'redbag_num' => 200 + 200 * $plateforms->ratio / 100,
                    'price' => 200,
                    'product_id' => 85,
                ],
            ],
        ];
    }
}