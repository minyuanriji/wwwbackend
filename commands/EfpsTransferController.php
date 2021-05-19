<?php
namespace app\commands;

use app\core\ApiCode;
use app\forms\efps\EfpsCashTransfer;
use app\forms\efps\EfpsMchCashTransfer;
use app\models\Cash;
use app\plugins\mch\models\MchCash;

class EfpsTransferController extends BaseCommandController{

    public function actionMaintantJob(){

        $this->mutiKill();

        echo date("Y-m-d H:i:s") . " 易票联提现转账守护程序启动...完成\n";

        while (true){

            $this->sleep(1);

            //商家提现转账
            $this->actionMchCashTransfer();

            //用户提现转账
            //$this->actionCashTransfer();
        }

    }

    /**
     * 商家提现转账
     * @return boolean
     */
    private function actionMchCashTransfer(){

        //拿出已同意待打款的记录
        $mchCash = MchCash::find()->where([
            "type"            => "efps_bank",
            "status"          => 1,
            "transfer_status" => 0
        ])->orderBy("updated_at ASC")->one();

        if(!$mchCash) return;

        $mchCash->updated_at = time();
        if(!$mchCash->save()) return false;

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

    /**
     * 用户提现转账
     * @return boolean
     */
    private function actionCashTransfer(){
        //拿出已同意待打款的记录
        $cash = Cash::find()->where([
            "type"      => "bank",
            "status"    => 1,
            "is_delete" => 0
        ])->orderBy("updated_at ASC")->one();

        if(!$cash) return;

        $cash->updated_at = time();
        if(!$cash->save()) return false;

        try {
            $res = EfpsCashTransfer::transfer($cash);
            if($res['code'] != ApiCode::CODE_SUCCESS) { //打款失败
                throw new \Exception($res['msg']);
            }

            $cash->is_transmitting = $res['is_transmitting'];
            $cash->status = 2;
            if (!$cash->save()) {
                throw new \Exception(json_encode($cash->getErrors()));
            }

            $this->commandOut("用户提现记录[ID:".$cash->id."]打款成功");

        }catch (\Exception $e){
            $this->commandOut($e->getMessage());
            $cash->content = $e->getMessage();
            $cash->save();
        }
    }
}