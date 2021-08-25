<?php

namespace app\commands;

use app\core\ApiCode;
use app\forms\common\UserIntegralForm;
use app\models\User;
use app\plugins\addcredit\forms\api\order\PhoneOrderRefundForm;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\addcredit\plateform\result\QueryResult;
use app\plugins\addcredit\plateform\sdk\k_default\Code as one_Code;
use app\plugins\addcredit\plateform\sdk\two_sdk\Code as two_Code;
use app\plugins\addcredit\plateform\sdk\k_default\PlateForm as one_PlateForm;
use app\plugins\addcredit\plateform\sdk\two_sdk\PlateForm as two_PlateForm;

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

        $plate_form = new one_PlateForm();
        foreach ($orderList as $item)
        {
            try {
                $query_res = $plate_form->query($item);
                if (!$query_res) {
                    throw new \Exception('未知错误！', ApiCode::CODE_FAIL);
                }
                if ($query_res->code != QueryResult::CODE_SUCC) {
                    throw new \Exception($query_res->message, ApiCode::CODE_FAIL);
                }
                $response_content = json_decode($query_res->response_content,true);
                try {
                    //成功，处理状态
                    $item->updated_at = time();
                    switch ($response_content['status'])
                    {
                        case one_Code::PAY_STATUS_PAID:
                            if ($response_content['arrival'] == one_Code::COMPLETE_STATUS_RECEIVED) {
                                $item->order_status = AddcreditOrder::ORDER_STATUS_SUC;
                                $item->plateform_request_data = $query_res->request_data;
                                $item->plateform_response_data = $query_res->response_content;
                                if (!$item->save()) {
                                    throw new \Exception("话费订单失败：" . json_encode($item->getErrors()), ApiCode::CODE_FAIL);
                                }
                            }
                            break;
                        case one_Code::PAY_STATUS_FAIL:
                            if ($response_content['arrival'] == one_Code::COMPLETE_STATUS_REFUNDED) {
                                $transaction = \Yii::$app->db->beginTransaction();
                                try {
                                    $item->pay_status = AddcreditOrder::PAY_TYPE_REFUND;
                                    $item->order_status = AddcreditOrder::ORDER_STATUS_FAIL;
                                    $item->plateform_request_data = $query_res->request_data;
                                    $item->plateform_response_data = $query_res->response_content;
                                    if (!$item->save()) {
                                        throw new \Exception("话费订单失败：" . json_encode($item->getErrors()), ApiCode::CODE_FAIL);
                                    }
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
                                    $transaction->commit();
                                } catch (\Exception $exception) {
                                    $transaction->rollBack();
                                    \Yii::error($exception->getLine().";file:".$exception->getFile());
                                    throw new \Exception($exception->getMessage(), ApiCode::CODE_FAIL);
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

    private function orderQueryOld ()
    {
        $orderList = AddcreditOrder::find()
            ->andWhere([
                'pay_status'    => AddcreditOrder::PAY_TYPE_PAID,
                'order_status'  => AddcreditOrder::ORDER_STATUS_PRO,
            ])
            ->limit(10)->all();

        if (!$orderList) return false;

        $plate_form = new two_PlateForm();
        foreach ($orderList as $item)
        {
            try {
                $query_res = $plate_form->query($item);
                if (!$query_res) {
                    throw new \Exception('未知错误！', ApiCode::CODE_FAIL);
                }
                if ($query_res->code != QueryResult::CODE_SUCC) {
                    throw new \Exception($query_res->message, ApiCode::CODE_FAIL);
                }
                $response_content = json_decode($query_res->response_content);
                try {
                    //成功，处理状态
                    $item->updated_at = time();
                    switch ($response_content->code)
                    {
                        case two_Code::QUERY_SUCCESS:
                            $item->order_status = AddcreditOrder::ORDER_STATUS_SUC;
                            $item->plateform_request_data = $query_res->request_data;
                            $item->plateform_response_data = $query_res->response_content;
                            if (!$item->save()) {
                                throw new \Exception("话费订单失败：" . json_encode($item->getErrors()), ApiCode::CODE_FAIL);
                            }
                            break;
                        case two_Code::QUERY_FAIL || two_Code::QUERY_REFUND:
                            $transaction = \Yii::$app->db->beginTransaction();
                            try {
                                $item->pay_status = AddcreditOrder::PAY_TYPE_REFUND;
                                $item->order_status = AddcreditOrder::ORDER_STATUS_FAIL;
                                $item->plateform_request_data = $query_res->request_data;
                                $item->plateform_response_data = $query_res->response_content;
                                if (!$item->save()) {
                                    throw new \Exception("话费订单失败：" . json_encode($item->getErrors()), ApiCode::CODE_FAIL);
                                }
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
                                $transaction->commit();
                            } catch (\Exception $exception) {
                                $transaction->rollBack();
                                \Yii::error($exception->getLine().";file:".$exception->getFile());
                                throw new \Exception($exception->getMessage(), ApiCode::CODE_FAIL);
                            }
                            break;
//                        case Code::QUERY_FREQUENTLY:
//                            throw new \Exception(Msg::QueryMsg()[$response_content->nRtn], ApiCode::CODE_FAIL);
                        case two_Code::QUERY_ORDER_EMPTY:
                            //再次下单
                            $plateform = AddcreditPlateforms::findOne($item->plateform_id);
                            if (!$plateform) {
                                throw new \Exception("无法获取平台信息", ApiCode::CODE_FAIL);
                            }
                            $plate_form = new two_PlateForm();
                            $submit_res = $plate_form->submit($item, $plateform);
                            if (!$submit_res) {
                                throw new \Exception('未知错误！', ApiCode::CODE_FAIL);
                            }
                            if ($submit_res->code != ApiCode::CODE_SUCCESS) {
                                throw new \Exception($submit_res->message, ApiCode::CODE_FAIL);
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
