<?php

namespace app\commands\mch_task_action;

use app\core\ApiCode;
use app\mch\forms\mch\MchAccountModifyForm;
use app\models\Mall;
use app\models\Order;
use app\models\OrderDetail;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchPriceLog;
use yii\base\Action;

class OrderPriceLogAction extends Action{

    public function run(){
        while(true) {
            if(!$this->doSuccess()){
                if(!$this->doCanceled()){
                    sleep(5);
                }
            }
        }
    }

    /**
     * 未结算，订单已确认收货
     * @return boolean
     */
    private function doSuccess(){
        $query = MchPriceLog::find()->alias("mpl")
                    ->innerJoin(["od" => OrderDetail::tableName()], "mpl.source_id=od.id")
                    ->innerJoin(["o" => Order::tableName()], "o.id=od.order_id");
        $query->andWhere([
            "AND",
            ["mpl.status" => "unconfirmed"],
            ["mpl.source_type" => "order_detail"],
            "od.is_refund=0 OR (od.is_refund=1 AND od.refund_status=21)",
            ["o.is_delete" => 0],
            ["o.is_recycle" => 0],
            ["IN", "o.status", [1, 2, 3, 6, 7, 8]],
            ["o.is_confirm" => 1]
        ]);

        $data = $query->select([
            "mpl.id", "mpl.mall_id", "mpl.mch_id", "mpl.store_id",
            "mpl.price", "mpl.content", "mpl.other_json_data"
        ])->asArray()->orderBy("mpl.updated_at ASC")->one();

        if(!$data){
            return false;
        }

        $otherData = (array)@json_decode($data['other_json_data']);

        MchPriceLog::updateAll(["updated_at" => time()], ["id" => $data['id']]);

        \Yii::$app->mall = Mall::findOne($data['mall_id']);

        $t = \Yii::$app->getDb()->beginTransaction();
        try {

            //修改商家帐户
            $mch = Mch::findOne($data['mch_id']);
            if($mch && !$mch->is_delete){
                $res = MchAccountModifyForm::modify($mch, $data['price'], $data['content'], true);
                if($res['code'] != ApiCode::CODE_SUCCESS){
                    $otherData['remark'] = $res['msg'];
                }
            }else{
                $otherData['remark'] = "无法获取商家[ID:".$data['mch_id']."]信息";
            }

            //设置结算记录状态为已成功
            MchPriceLog::updateAll([
                "updated_at"      => time(),
                "status"          => "success",
                "other_json_data" => json_encode($otherData)
            ], ["id" => $data['id']]);

            $t->commit();

            $this->controller->commandOut("商家订单结算记录[ID:".$data['id']."]处理成功");

        }catch (\Exception $e){
            $t->rollBack();
            $this->controller->commandOut($e->getMessage());
        }
    }

    /**
     * 未结算，已退款
     * @return boolean
     */
    private function doCanceled(){
        $query = MchPriceLog::find()->alias("mpl")
            ->innerJoin(["od" => OrderDetail::tableName()], "mpl.source_id=od.id")
            ->innerJoin(["o" => Order::tableName()], "o.id=od.order_id");
        $query->where([
            "mpl.status" => "unconfirmed",
            "mpl.source_type" => "order_detail"
        ]);
        $query->andWhere([
            "OR",
            ["o.is_delete" => 1],
            ["o.is_recycle" => 1],
            ["IN", "o.status", [5]],
            "od.is_refund=1 AND (od.refund_status IN(11, 12, 20))"
        ]);

        $idArray = $query->select(["mpl.id"])->column();
        if($idArray){
            MchPriceLog::updateAll([
                "status" => "canceled",
                "updated_at" => time()
            ], ["IN", "id", $idArray]);
            return true;
        }
        return false;
    }
}