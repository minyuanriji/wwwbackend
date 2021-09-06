<?php

namespace app\commands\commission_action;

use app\core\ApiCode;
use app\forms\common\UserIncomeCommissionAddcredit3rForm;
use app\forms\common\UserIncomeCommissionHotel3rForm;
use app\models\User;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\commission\models\CommissionAddcredit3rPriceLog;
use app\plugins\commission\models\CommissionHotel3rPriceLog;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\hotel\models\Hotels;
use yii\base\Action;
use yii\base\BaseObject;

class Addcredit3rAction extends Action
{

    public function run()
    {
        while (true) {
            $this->doNew();
        }
    }

    //新增操作
    private function doNew()
    {
        $addcreditOrder = AddcreditOrder::find()->andWhere([
            "AND",
            ["pay_status" => "paid"],
            ["order_status" => "success"],
            ["commission_3r_status" => 0]
        ])->orderBy("updated_at ASC")->one();

        if (!$addcreditOrder) return false;

        //更新时间，防止一个出错反复执行
        $addcreditOrder->updated_at = time();
        $addcreditOrder->save();

        $trans = \Yii::$app->db->beginTransaction();
        try {

            //获取酒店信息
            $plateforms = AddcreditPlateforms::findOne($addcreditOrder->plateform_id);
            if (!$plateforms) {
                $addcreditOrder->commission_status = 1;
                throw new \Exception("话费平台不存在");
            }

            //计算利润
//            $profit = max(0, $addcreditOrder->order_price * ($plateforms->transfer_rate / 100 - 0.1) * 0.6);
            $profit = max(0, $addcreditOrder->order_price * (1 - $plateforms->transfer_rate / 100 - 0.1 + $plateforms->ratio / 100) * 0.5);
            if ($profit <= 0) {
                $addcreditOrder->commission_3r_status = 1;
                throw new \Exception("利润小或等于0无法分佣");
            }

            //要分佣的父数据
            $parentDatas = $this->controller->getCommissionParentRuleDatas($addcreditOrder->user_id, $plateforms->id, 'addcredit_3r');

            //通过相关规则键获取分佣规则进行分佣
            foreach ($parentDatas as $parentData) {
                $ruleData = $parentData['rule_data'];

                //无分佣规则 跳过
                if (!$ruleData) continue;

                //父级用户信息
                $user = User::findOne($parentData['id']);
                if (!$user || $user->is_delete) {
                    continue;
                }

                //计算分佣金额
                if ($ruleData['commission_type'] == 1) { //按百分比
                    $price = (floatval($ruleData['commisson_value']) / 100) * floatval($profit);
                } else { //按固定值
                    $price = (float)$ruleData['commisson_value'];
                }

                //话费生成结算分佣记录
                $uniqueData = [
                    'mall_id' => $addcreditOrder->mall_id,
                    'addcredit_order_id' => $addcreditOrder->id,
                    'user_id' => $parentData['id'],
                ];
                $priceLog = CommissionAddcredit3rPriceLog::findOne($uniqueData);
                if (!$priceLog) {
                    $priceLog = new CommissionAddcredit3rPriceLog(array_merge($uniqueData, [
                        'created_at' => time(),
                        'updated_at' => time(),
                        'price' => $price,
                        'status' => 1,
                        'rule_data_json' => json_encode(json_encode($ruleData))
                    ]));
                    if (!$priceLog->save()) {
                        throw new \Exception(json_encode($priceLog->getErrors()));
                    }

                    $res = UserIncomeCommissionAddcredit3rForm::AddcreditCommissionFzAdd($user, $addcreditOrder, $priceLog, false);
                    if ($res['code'] != ApiCode::CODE_SUCCESS) {
                        throw new \Exception($res['msg']);
                    }
                }
            }

            $addcreditOrder->commission_3r_status = 1;

            $trans->commit();

        } catch (\Exception $e) {
            $trans->rollBack();
            $addcreditOrder->commission_3r_remark = substr($e->getMessage(), 0, 255);
            $this->controller->commandOut($e->getMessage());
        }

        $addcreditOrder->updated_at = time();
        if (!$addcreditOrder->save()) {
            $this->controller->commandOut(json_encode($addcreditOrder->getErrors()));
        }

        $this->controller->commandOut("新增话费订单：" . $addcreditOrder->id . "消费分佣记录");

        return true;
    }
}