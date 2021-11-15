<?php

namespace app\commands\shopping_voucher_send_task;

use app\models\User;
use app\plugins\oil\models\OilOrders;
use app\plugins\oil\models\OilPlateforms;
use app\plugins\oil\models\OilProduct;
use app\plugins\shopping_voucher\forms\common\ShoppingVoucherLogModifiyForm;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromOil;
use app\plugins\shopping_voucher\models\ShoppingVoucherLog;
use app\plugins\shopping_voucher\models\ShoppingVoucherSendLog;
use yii\base\Action;

class OilOrderSendAction extends Action{

    public function run(){
        $this->controller->commandOut(date("Y/m/d H:i:s") . " OilOrderSendAction start");
        while (true){
            try {
                if(!$this->newAction()){
                    $this->sendAction();
                }
            }catch (\Exception $e){
                $this->controller->commandOut(implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]));
            }
            $this->controller->sleep(1);
        }
    }

    /**
     * 处理发放记录
     * @return void
     */
    private function sendAction(){
        $sendLogs = ShoppingVoucherSendLog::find()->where(["status" => "waiting", "source_type" => "from_oil_order"])
            ->select(["id", "user_id", "source_id", "source_type", "money"])
            ->orderBy("updated_at ASC")
            ->asArray()->limit(10)->all();
        $sendLogIds = [];
        foreach($sendLogs as $sendLog){
            try {
                $user = User::findOne($sendLog['user_id']);
                if(!$user || $user->is_delete){
                    throw new \Exception("用户不存在");
                }
                $modifyForm = new ShoppingVoucherLogModifiyForm([
                    "money"       => $sendLog['money'],
                    "desc"        => "支付加油券获得赠送购物券",
                    "source_id"   => $sendLog['source_id'],
                    "source_type" => $sendLog['source_type']
                ]);
                $modifyForm->add($user, true);
                $sendLogIds[] = $sendLog['id'];
                $this->controller->commandOut("购物券发放记录ID:" . $sendLog['id'] . "处理完成");
            }catch (\Exception $e){
                $remark = implode("\n", [$e->getMessage(), "line:" . $e->getLine(), "file:".$e->getFile()]);
                ShoppingVoucherSendLog::updateAll([
                    "status" => "invalid",
                    "remark" => $remark
                ], ["id" => $sendLog['id']]);
                $this->controller->commandOut($remark);
            }
        }
        if($sendLogIds){
            ShoppingVoucherSendLog::updateAll(["status" => "success"], "id IN (".implode(",", $sendLogIds).")");
        }
    }

    /**
     * 新增发送记录
     * @return bool
     */
    private function newAction(){

        $query = OilOrders::find()->alias("oo");
        $query->innerJoin(["opp" => OilProduct::tableName()], "opp.id=oo.product_id");
        $query->innerJoin(["op" => OilPlateforms::tableName()], "op.id=opp.plat_id");
        $query->leftJoin(["svs" => ShoppingVoucherSendLog::tableName()], "svs.source_id=oo.id AND svs.source_type='from_oil_order'");
        $query->andWhere([
            "AND",
            "oo.pay_price > 0",
            "svs.id IS NULL",
            "oo.created_at>svfo.start_at",
            ["oo.pay_status" => 'paid'],
            ["IN", "oo.order_status", ['finished','fail','unconfirmed','wait']],
        ]);

        $query->orderBy("oo.updated_at ASC");
        $selects = [
            "oo.id", "oo.mall_id", "oo.user_id", "oo.pay_price", "oo.product_id", "opp.plat_id",
            "svfo.first_give_type", "svfo.first_give_value",
            "svfo.second_give_type", "svfo.second_give_value"
        ];
        $query->select($selects)->asArray()->limit(10);

        //指定平台-全部产品
        $cloneQuery = clone $query;
        $cloneQuery->innerJoin(["svfo" => ShoppingVoucherFromOil::tableName()], "svfo.plat_id=opp.plat_id AND svfo.is_delete=0");
        $oilOrders = $cloneQuery->all();

        //通用配置-全平台-全产品
        if(!$oilOrders){
            $cloneQuery = clone $query;
            $cloneQuery->innerJoin(["svfo" => ShoppingVoucherFromOil::tableName()], "svfo.plat_id=0 AND svfo.product_id=0 AND svfo.is_delete=0");
            $oilOrders = $cloneQuery->all();
        }

        if(!$oilOrders)
            return false;

        $oilOrderIds = [];
        foreach($oilOrders as $oilOrder){
            $oilOrderIds[] = $oilOrder['id'];
        }
        OilOrders::updateAll(["updated_at" => time()], "id IN (".implode(",", $oilOrderIds).")");

        foreach($oilOrders as $oilOrder){

            //通过查询历史赠送记录，判断用户是否首次赠送购物券
            $sendLog = ShoppingVoucherLog::find()->where([
                "user_id"     => $oilOrder['user_id'],
                "type"        => 1,
                "source_type" => "from_oil_order"
            ])->one();
            $isFirst = !$sendLog ? true : false;

            if($isFirst){ //首次赠送
                $oilOrder['give_type']  = $oilOrder['first_give_type'];
                $oilOrder['give_value'] = $oilOrder['first_give_value'];
            }else{ //第二次赠送
                $oilOrder['give_type']  = $oilOrder['second_give_type'];
                $oilOrder['give_value'] = $oilOrder['second_give_value'];
            }

            if($oilOrder['give_type'] == 2){ //固定值
                $money = floatval($oilOrder['give_value']);
            }else{ //比例
                $money = $oilOrder['pay_price'] * (floatval($oilOrder['give_value'])/100);
            }

            //上限100，超过100部分按50%计算
            if($money > 100){
                $money = 100 + ($money - 100) * 0.5;
            }

            $sendLog = new ShoppingVoucherSendLog([
                "mall_id"     => $oilOrder['mall_id'],
                "user_id"     => $oilOrder['user_id'],
                "money"       => $money,
                "source_id"   => $oilOrder['id'],
                "source_type" => "from_oil_order",
                "status"      => "waiting",
                "created_at"  => time(),
                "updated_at"  => time(),
                "data_json"   => json_encode($oilOrder)
            ]);

            if($sendLog->save()){
                $this->controller->commandOut("购物券发放记录创建成功，ID:" . $sendLog->id);
            }else{
                $this->controller->commandOut(json_encode($sendLog->getErrors()));
            }
        }

        return true;
    }

}