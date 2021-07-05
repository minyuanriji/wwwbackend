<?php
namespace app\commands\efps_task_action;

use app\core\ApiCode;
use app\forms\efps\EfpsMchCashTransfer;
use app\models\EfpsTransferOrder;
use app\plugins\mch\models\MchCash;
use yii\base\Action;

/**
 * 商户提现提交易票联处理
 * @package app\commands\efps_task_action
 */
class MchCashTransferCommitAction extends Action{

    public function run(){
        echo date("Y/m/d H:i:s") . "\n<MchCashTransferCommitAction> start\n";
        while (true){

            try {
                //同意未提交
                $query = MchCash::find()->alias("mc");
                $query->leftJoin(["eto" => EfpsTransferOrder::tableName()], "eto.outTradeNo=mc.order_no");
                $query->select(["mc.id"]);
                $query->andWhere([
                    "AND",
                    ["mc.type" => "efps_bank"],
                    ["mc.status" => 1],
                    ["mc.is_delete" => 0],
                    ["mc.transfer_status" => 0],
                    "eto.id IS NULL OR `eto`.`status` = 0"
                ]);

                $data = $query->orderBy("mc.updated_at ASC")->one();
                if(!$data){
                    sleep(30);
                    continue;
                }

                $mchCash = MchCash::findOne($data['id']);
                $mchCash->updated_at = time();
                if(!$mchCash->save()){
                    throw new \Exception(json_encode($mchCash->getErrors()));
                }

                //提交易票联进行打款处理
                $res = EfpsMchCashTransfer::commit($mchCash);
                if($res['code'] != ApiCode::CODE_SUCCESS) {
                    throw new \Exception($res['msg']);
                }

                echo ("Mch ".$mchCash->mch_id." withdraw commit process success[ID:".$mchCash->id."]\n");
            }catch (\Exception $e){
                $message[] = $e->getMessage();
                $message[] = "File:" . $e->getFile();
                $message[] = "Line:" . $e->getLine();
                echo (implode(" ", $message) . "\n");
            }

        }
    }
}