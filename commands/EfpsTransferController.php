<?php
namespace app\commands;

use app\core\ApiCode;
use app\forms\efps\EfpsMchCashTransfer;
use app\plugins\mch\models\MchCash;

class EfpsTransferController extends BaseCommandController{

    public function actionMaintantJob(){

        $this->mutiKill();

        echo date("Y-m-d H:i:s") . " 易票联提现转账守护程序启动...完成\n";

        while (true){

            $this->sleep(1);

            //拿出已同意待打款的记录
            $mchCash = MchCash::find()->where([
                "type"            => "efps_bank",
                "status"          => 1,
                "transfer_status" => 0
            ])->orderBy("updated_at ASC")->one();

            if(!$mchCash) continue;

            $mchCash->updated_at = time();
            $mchCash->save();

            try {
                $res = EfpsMchCashTransfer::transfer($mchCash);
                if($res['code'] != ApiCode::CODE_SUCCESS) { //打款失败
                    throw new \Exception($res['msg']);
                }

                $mchCash->status = 1;
                $mchCash->transfer_status = 1;
                if (!$mchCash->save()) {
                    throw new \Exception(json_encode($mchCash->getErrors()));
                }

                $this->commandOut("商户提现记录[ID:".$mchCash->id."]打款成功");

            }catch (\Exception $e){
                $this->commandOut($e->getMessage());
                $mchCash->content = $e->getMessage();
                if($mchCash->retry_count >= 3){
                    $mchCash->status = 2;
                }
                $mchCash->save();
            }
        }

    }

}