<?php


namespace app\commands\commission_action;


use app\commands\CommissionController;
use app\core\ApiCode;
use app\forms\common\UserIncomeCommissionGiftpacksForm;
use app\models\User;
use app\plugins\commission\models\CommissionGiftpacksPriceLog;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksOrder;
use yii\base\Action;

class GiftpacksAction extends Action{

    public function run(){
        while (true){
            sleep(1);
            $this->doNew(); //店铺二维码收款分佣
        }
    }

    /**
     * 大礼包订单，新增分佣记录
     * @return boolean
     */
    private function doNew(){

        //已付款未分佣
        $query = GiftpacksOrder::find()->alias("gfo")->where([
            "gfo.pay_status" => "paid",
            "gfo.is_delete"  => 0,
            "gfo.commission_status" => 0
        ]);
        $query->innerJoin(["gf" => Giftpacks::tableName()], "gf.id=gfo.pack_id");
        $orderData = $query->orderBy("gfo.updated_at ASC")->asArray()->select([
            "gfo.id", "gfo.mall_id", "gfo.user_id", "gfo.pack_id", "gf.title", "gf.profit_price"
        ])->one();
        if(!$orderData){
            return false;
        }

        $doCommissionStatus = 0;
        $doCommissionRemark = "";

        //更新时间
        GiftpacksOrder::updateAll(["updated_at" => time()], ["id" => $orderData['id']]);

        $t = \Yii::$app->db->beginTransaction();
        try {
            $parentDatas = $this->controller->getCommissionParentRuleDatas($orderData['user_id'], $orderData['pack_id'], 'giftpacks');

            //通过相关规则键获取分佣规则进行分佣
            foreach($parentDatas as $parentData){
                $ruleData = $parentData['rule_data'];

                //无分佣规则 跳过
                if(!$ruleData) continue;

                //计算分佣金额
                $ruleData['profit_price'] = $orderData['profit_price'];
                if($ruleData['commission_type'] == 1){ //按百分比
                    $price = (floatval($ruleData['commisson_value'])/100) * floatval($ruleData['profit_price']);
                }else{ //按固定值
                    $price = (float)$ruleData['commisson_value'];
                }

                //分佣金额小于0，无法分佣
                if($price <= 0) continue;



                //获取待分佣的上级用户数据对象
                $parentUser = User::findOne($parentData['id']);
                if(!$parentUser || $parentUser->is_delete){
                    continue;
                }

                //生成分佣记录
                $uniqueData = [
                    "pack_id"  => $orderData['pack_id'],
                    "user_id"  => $parentData['id'],
                    "order_id" => $orderData['id'],
                    "mall_id"  => $orderData['mall_id']
                ];
                $priceLog = CommissionGiftpacksPriceLog::findOne($uniqueData);
                if(!$priceLog){
                    $priceLog = new CommissionGiftpacksPriceLog(array_merge($uniqueData, [
                        "status"         => 1, //直接已结算
                        "created_at"     => time(),
                        "updated_at"     => time(),
                        "price"          => round($price, 5),
                        "rule_data_json" => json_encode($ruleData)
                    ]));
                    if(!$priceLog->save()){
                        throw new \Exception(json_encode($priceLog->getErrors()));
                    }
                    $this->controller->commandOut("生成大礼包分佣记录 [ID:".$priceLog->id."]");

                    //收益变更
                    $res = UserIncomeCommissionGiftpacksForm::doCommissionAdd($parentUser, $orderData['title'], $priceLog, false);
                    if($res['code'] != ApiCode::CODE_SUCCESS){
                        throw new \Exception($res['msg']);
                    }
                }
            }

            $t->commit();

            $doCommissionStatus = 1;

        }catch (\Exception $e){
            $t->rollBack();
            $this->controller->commandOut($e->getMessage());
            $this->controller->commandOut("File:" . $e->getFile());
            $this->controller->commandOut("Line:" . $e->getLine());
            $this->controller->commandOut("CODE:" . $e->getCode());

            //无法找到上级的处理方案
            if($e->getCode() == CommissionController::ERR_CODE_NOT_FOUND_PARENTS){
                $doCommissionStatus = 1;
                $doCommissionRemark = $e->getMessage();
            }
        }

        //更新分佣状态
        if($doCommissionStatus){
            GiftpacksOrder::updateAll([
                "updated_at"        => time(),
                "commission_status" => 1,
                "commission_remark" => $doCommissionRemark
            ], ["id" => $orderData['id']]);
        }

        return true;
    }
}