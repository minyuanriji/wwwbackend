<?php

namespace app\commands\shopping_voucher_send_task;

use app\plugins\oil\models\OilOrders;
use app\plugins\oil\models\OilPlateforms;
use app\plugins\oil\models\OilProduct;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromOil;
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
    private function sendAction(){}

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

        }

        return true;
    }

}