<?php

namespace app\commands;

use app\core\ApiCode;
use app\forms\common\UserIntegralForm;
use app\models\User;
use app\plugins\addcredit\forms\api\order\PhoneOrderRefundForm;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\plateform\sdk\k_default\Code;
use app\plugins\addcredit\plateform\sdk\k_default\PlateForm;

class TelephoneOrderController extends BaseCommandController
{

    public function actionMaintantJob()
    {
        $this->orderQuery();die;
        $this->mutiKill();

        echo date("Y-m-d H:i:s") . " 话费订单查询中...\n";

        while (true) {
            $this->sleep(1);
            try {

                //检查订单是否已完成
                $this->orderQuery();

            } catch (\Exception $e) {
                $this->commandOut($e->getMessage());
            }
        }
    }

    private function orderQuery ()
    {
        $orderList = AddcreditOrder::find()
            ->andWhere([
                'pay_status'    => AddcreditOrder::PAY_TYPE_PAID,
                'order_status'  => AddcreditOrder::ORDER_STATUS_PRO,
            ])
            ->limit(10)->all();

        if (!$orderList) {
            return false;
        }

        $plate_form = new PlateForm();
        foreach ($orderList as $item)
        {
            try {
                $query_res = $plate_form->query($item);
                if (!$query_res) {
                    throw new \Exception('未知错误！', ApiCode::CODE_FAIL);
                }
                if ($query_res->code != Code::QUERY_SUCCESS) {
                    throw new \Exception($query_res->message, ApiCode::CODE_FAIL);
                }
                $response_content = json_decode($query_res->response_content);
                $trans = \Yii::$app->db->beginTransaction();
                try {
                    if ($response_content['status'] == 3 && $response_content['arrival'] == 2) {
                        $item->order_status = AddcreditOrder::ORDER_STATUS_SUC;
                        $item->updated_at = time();
                    }
                    if ($response_content['arrival'] == 3) {
                        $item->pay_status = AddcreditOrder::PAY_TYPE_REFUND;
                        $item->order_status = AddcreditOrder::ORDER_STATUS_FAIL;

                        $PhoneOrderRefundForm = new PhoneOrderRefundForm();
                        $refund_res = $PhoneOrderRefundForm->save($item->mall_id, $item->id, $item->integral_deduction_price);
                        if (isset($refund_res['code']) && $refund_res['code']) {
                            throw new \Exception($refund_res['msg'], ApiCode::CODE_FAIL);
                        }

                        //用户
                        $user = User::findOne($item->user_id);
                        if (!$user || $user->is_delete) {
                            throw new \Exception("无法获取用户信息", ApiCode::CODE_FAIL);
                        }

                        //返还红包
                        $res = UserIntegralForm::PhoneBillOrderRefundAdd($user, $item->integral_deduction_price, $item->id);
                        if ($res['code'] != ApiCode::CODE_SUCCESS) {
                            throw new \Exception("红包返还失败：" . $res['msg'], ApiCode::CODE_FAIL);
                        }
                    }
                    if (!$item->save()) {
                        throw new \Exception("话费订单失败：" . json_encode($item->getErrors()), ApiCode::CODE_FAIL);
                    }
                    $trans->commit();
                } catch (\Exception $e) {
                    $trans->rollBack();
                    $this->commandOut($e->getMessage());
                }
            } catch (\Exception $e) {
                $this->commandOut($e->getMessage());
            }
        }
        return true;
    }
}