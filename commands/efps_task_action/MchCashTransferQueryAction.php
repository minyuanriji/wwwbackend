<?php
namespace app\commands\efps_task_action;


use app\core\ApiCode;
use app\forms\efps\EfpsTransfer;
use app\models\EfpsTransferOrder;
use app\plugins\mch\models\MchCash;
use yii\base\Action;

class MchCashTransferQueryAction extends Action{

    public function run(){
        echo date("Y/m/d H:i:s") . "\n<MchCashTransferQueryAction> start\n";
        while (true){
            try {
                //同意已提交
                $query = MchCash::find()->alias("mc");
                $query->leftJoin(["eto" => EfpsTransferOrder::tableName()], "eto.outTradeNo=mc.order_no");
                $query->select(["mc.id", "eto.outTradeNo"]);
                $query->andWhere([
                    "AND",
                    ["mc.type" => "efps_bank"],
                    ["mc.status" => 1],
                    ["mc.transfer_status" => 0],
                    ["mc.is_delete" => 0],
                    "eto.id IS NOT NULL"
                ]);

                $data = $query->asArray()->orderBy("mc.updated_at ASC")->one();
                if(!$data){
                    sleep(30);
                    continue;
                }

                $mchCash = MchCash::findOne($data['id']);
                $mchCash->updated_at = time();
                if(!$mchCash->save()){
                    throw new \Exception(json_encode($mchCash->getErrors()));
                }

                $res = EfpsTransfer::query($data['outTradeNo']);
                $mchCash->content = $res['msg'];
                if($res['code'] == ApiCode::CODE_SUCCESS){ //打款成功
                    $mchCash->transfer_status = 1;
                }else{ //打款失败
                    $mchCash->status = 0;
                }
                if(!$mchCash->save()){
                    throw new \Exception(json_encode($mchCash->getErrors()));
                }

                echo ("Mch ".$mchCash->mch_id." withdraw query success[ID:".$mchCash->id."]\n");
            }catch (\Exception $e){
                $message[] = $e->getMessage();
                $message[] = "File:" . $e->getFile();
                $message[] = "Line:" . $e->getLine();
                echo (implode(" ", $message) . "\n");
            }
        }
    }

}