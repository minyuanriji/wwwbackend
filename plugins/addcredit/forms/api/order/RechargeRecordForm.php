<?php

namespace app\plugins\addcredit\forms\api\order;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\addcredit\plateform\sdk\qyj_sdk\PlateForm;

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
                'user_id' => \Yii::$app->user->id,
                'mall_id' => \Yii::$app->mall->id,
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

            if ($result) {
                foreach ($result as &$item) {
                    if ($item['pay_status'] == 'paid') {
                        if ($item['order_status'] == 'success') {
                            $item['status'] = '充值成功';
                        } elseif ($item['order_status'] == 'processing') {
                            $item['status'] = '充值中...';
                        } elseif ($item['order_status'] == 'fail') {
                            $item['status'] = '失败';
                        }
                    } elseif ($item['pay_status'] == 'unpaid') {
                        $item['status'] = '未支付';
                    } elseif ($item['pay_status'] == 'refund') {
                        $item['status'] = '已退款';
                    } elseif ($item['pay_status'] == 'refunding') {
                        $item['status'] = '退款中...';
                    }
                }
            }

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

        $PlateForm = new PlateForm();
        $goodsDetail = $PlateForm->getGoodsDetail($plateforms);
        if ($goodsDetail->code == ApiCode::CODE_FAIL) {
            throw new \Exception($goodsDetail->message);
        }

        $goodsData = json_decode($goodsDetail->response_content, true);
        $goodsList = [];
        foreach ($goodsData['data']['spec'] as $item) {
            $goodsList[] = [
                'redbag_num' => $item['official_price'] + $item['official_price'] * $plateforms->ratio / 100,
                'price' => $item['official_price'],
                'gid' => $goodsData['data']['g_id'],
                'specId' => $item['spec_id'],
            ];
        }
        return $goodsList;
    }
}