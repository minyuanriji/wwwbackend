<?php

namespace app\commands;

use app\core\ApiCode;
use app\forms\common\UserIntegralForm;
use app\models\User;
use app\plugins\addcredit\forms\api\order\PhoneOrderRefundForm;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\plateform\result\QueryResult;
use app\plugins\addcredit\plateform\sdk\qyj_sdk\Code;
use app\plugins\addcredit\plateform\sdk\qyj_sdk\PlateForm;

class TelephoneOrderController extends BaseCommandController
{
    public function actionMaintantJob()
    {
        $this->mutiKill();

        echo date("Y-m-d H:i:s") . " 话费订单查询中...\n";

        while (true) {
            $this->sleep(1);
            try {
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

        if (!$orderList) return false;

        $plate_form = new PlateForm();
        foreach ($orderList as $item)
        {
            try {
                $query_res = $plate_form->query($item);
                if (!$query_res) {
                    throw new \Exception('未知错误！');
                }
                if ($query_res->code != QueryResult::CODE_SUCC) {
                    throw new \Exception($query_res->message);
                }
                $response_content = json_decode($query_res->response_content,true);
                try {
                    //成功，处理状态
                    $item->updated_at = time();
                    switch ($response_content['status'])
                    {
                        case Code::PAY_STATUS_SUCCESS:
                            if ($response_content['arrival'] == Code::COMPLETE_STATUS_RECEIVED) {
                                $item->order_status = AddcreditOrder::ORDER_STATUS_SUC;
                                $item->plateform_request_data = $query_res->request_data;
                                $item->plateform_response_data = $query_res->response_content;
                                if (!$item->save()) {
                                    throw new \Exception("话费订单失败：" . json_encode($item->getErrors()));
                                }
                            }
                            break;
                        case Code::PAY_STATUS_FAIL:
                            if ($response_content['arrival'] == Code::COMPLETE_STATUS_REFUNDED || $response_content['arrival'] == Code::COMPLETE_STATUS_NON_ARRIVAL) {
                                $transaction = \Yii::$app->db->beginTransaction();
                                try {
                                    $item->pay_status = AddcreditOrder::PAY_TYPE_REFUND;
                                    $item->order_status = AddcreditOrder::ORDER_STATUS_FAIL;
                                    $item->plateform_request_data = $query_res->request_data;
                                    $item->plateform_response_data = $query_res->response_content;
                                    if (!$item->save()) {
                                        throw new \Exception("话费订单失败：" . json_encode($item->getErrors()));
                                    }
                                    $PhoneOrderRefundForm = new PhoneOrderRefundForm();
                                    $refund_res = $PhoneOrderRefundForm->save($item->mall_id, $item->id, $item->integral_deduction_price);
                                    if (isset($refund_res['code']) && $refund_res['code']) {
                                        throw new \Exception($refund_res['msg']);
                                    }

                                    //用户
                                    $user = User::findOne($item->user_id);
                                    if (!$user || $user->is_delete) {
                                        throw new \Exception("无法获取用户信息");
                                    }

                                    //返还红包
                                    $res = UserIntegralForm::PhoneBillOrderRefundAdd($user, $item->integral_deduction_price, $item->id);
                                    if ($res['code'] != ApiCode::CODE_SUCCESS) {
                                        throw new \Exception("红包返还失败：" . $res['msg']);
                                    }
                                    $transaction->commit();
                                } catch (\Exception $exception) {
                                    $transaction->rollBack();
                                    \Yii::error($exception->getLine().";file:".$exception->getFile());
                                    throw new \Exception($exception->getMessage());
                                }
                            }
                            break;
                        default:
                            break;
                    }

                } catch (\Exception $e) {
                    $this->commandOut($e->getMessage());
                }
            } catch (\Exception $e) {
                $this->commandOut($e->getMessage());
            }
        }
        return true;
    }
}
