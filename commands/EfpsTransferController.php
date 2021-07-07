<?php
namespace app\commands;

use app\core\ApiCode;
use app\forms\efps\EfpsCashTransfer;
use app\models\Cash;

class EfpsTransferController extends BaseCommandController{

    public function actionMaintantJob(){

        $this->mutiKill();

        echo date("Y-m-d H:i:s") . " 易票联提现转账守护程序启动...完成\n";

        while (true){

            $this->sleep(1);

            //用户提现转账
            //$this->actionCashTransfer();
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