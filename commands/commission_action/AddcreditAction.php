<?php

namespace app\commands\commission_action;

use app\core\ApiCode;
use app\forms\common\UserIncomeCommissionAddcreditForm;
use app\models\User;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\commission\models\CommissionAddcreditPriceLog;
use app\plugins\commission\models\CommissionRuleChain;
use app\plugins\commission\models\CommissionRules;
use yii\base\Action;

class AddcreditAction extends Action
{
    public function run ()
    {
        $this->doNew();

        /*while (true) {
            $this->doNew();
        }*/
    }

    //新增话费分佣结算记录
    private function doNew ()
    {
        $addcreditOrder = AddcreditOrder::find()
            ->andWhere([
                "AND",
                ["pay_status" => "paid"],
                ["order_status" => "success"],
                ["commission_status" => 0]
            ])->orderBy("updated_at ASC")->one();

        if (!$addcreditOrder) return false;

        //更新时间，防止一个出错反复执行
        $addcreditOrder->updated_at = time();
        $addcreditOrder->save();

        $trans = \Yii::$app->db->beginTransaction();
        try {

            //获取话费信息
            $plateforms = AddcreditPlateforms::findOne($addcreditOrder->plateform_id);
            if (!$plateforms) {
                $addcreditOrder->commission_status = 1;
                throw new \Exception("话费平台不存在");
            }

            //话费推荐人
            $recommander = User::findOne($plateforms->parent_id);
            if (!$recommander || $recommander->is_delete) {
                $addcreditOrder->commission_status = 1;
                throw new \Exception("推荐人不存在");
            }

            //计算利润
            $profit = max(0, $addcreditOrder->order_price * ($plateforms->transfer_rate / 100 - 0.1) * 0.6);

            if ($profit <= 0) {
                $addcreditOrder->commission_status = 1;
                throw new \Exception("利润小或等于0无法分佣");
            }
            //独立分佣规则
            $rule = CommissionRules::find()->where([
                "item_type" => "addcredit",
                "item_id" => $plateforms->id,
                "apply_all_item" => 0,
                "is_delete" => 0
            ])->one();

            if (!$rule) {
                $rule = CommissionRules::find()->where([
                    "item_type" => "addcredit",
                    "apply_all_item" => 1,
                    "is_delete" => 0
                ])->one();
            }

            if (!$rule) {
                $addcreditOrder->commission_status = 1;
                throw new \Exception("分佣规则不存在");
            }

            //根据所属等级获取规则链
            $ruleChain = CommissionRuleChain::find()->where([
                "rule_id" => $rule->id,
                "level" => 1,
                'role_type' => $recommander->role_type,
                "unique_key" => $recommander->role_type . "#all"
            ])->one();
            if (!$ruleChain) {
                $addcreditOrder->commission_status = 1;
                throw new \Exception("分佣规则链<" . $recommander->role_type . "#all>不存在");
            }

            //计算分佣金额
            if ($rule->commission_type == 1) { //按百分比
                $price = (floatval($ruleChain->commisson_value) / 100) * floatval($profit);
            } else { //按固定值
                $price = (float)$ruleChain->commisson_value;
            }
            if ($price <= 0) {
                $addcreditOrder->commission_status = 1;
                throw new \Exception("分佣金额小或等于0无法分佣");
            }

            $uniqueData = [
                'mall_id' => $addcreditOrder->mall_id,
                'addcredit_order_id' => $addcreditOrder->id,
                'user_id' => $recommander->id,
            ];
            $priceLog = CommissionAddcreditPriceLog::findOne($uniqueData);
            if (!$priceLog) {
                $priceLog = new CommissionAddcreditPriceLog(array_merge($uniqueData, [
                    'created_at' => time(),
                    'updated_at' => time(),
                    'price' => $price,
                    'status' => 1,
                    'rule_data_json' => json_encode(array_merge($rule->getAttributes(), $ruleChain->getAttributes()))
                ]));
                if (!$priceLog->save()) {
                    throw new \Exception(json_encode($priceLog->getErrors()));
                }

                $res = UserIncomeCommissionAddcreditForm::AddcreditCommissionFzAdd($recommander, $addcreditOrder, $priceLog, false);
                if ($res['code'] != ApiCode::CODE_SUCCESS) {
                    throw new \Exception($res['msg']);
                }
            }

            $addcreditOrder->commission_status = 1;

            $trans->commit();

        } catch (\Exception $e) {
            $trans->rollBack();
            $addcreditOrder->commission_remark = substr($e->getMessage(), 0, 255);
            $this->controller->commandOut($e->getMessage());
        }

        $addcreditOrder->updated_at = time();
        if (!$addcreditOrder->save()) {
            $this->controller->commandOut(json_encode($addcreditOrder->getErrors()));
        }

        $this->controller->commandOut("新增话费订单：" . $addcreditOrder->id . "推荐分佣记录");

        return true;
    }
}