<?php

namespace app\commands\oil_task;

use app\core\ApiCode;
use app\forms\efps\EfpsTransfer;
use app\forms\efps\EfpsTransferData;
use app\plugins\oil\models\OilJiayoulaTransferOrder;
use yii\base\Action;
use yii\db\Exception;

class JiayoulaTransferAction extends Action{

    public function run(){
        while(true){
            try {

                if(!$this->transmitCommit()){
                    $this->commitQuery();
                }

            }catch (\Exception $e){
                echo $e->getMessage() . "\n";
            }
            sleep(1);
        }
    }

    /**
     * 提交打款处理结果查询
     * @return void
     */
    public function commitQuery(){
        $transferOrder = OilJiayoulaTransferOrder::find()->where([
            "status" => "transmitting"
        ])->orderBy("updated_at DESC")->one();
        if(!$transferOrder)
            return;

        $transferOrder->updated_at = time();
        $res = EfpsTransfer::query($transferOrder->order_sn);
        if($res['code'] == ApiCode::CODE_SUCCESS){ //打款成功
            $transferOrder->status = "paid";
        }else{
            $this->controller->commandOut($res['msg']);
        }
        if(!$transferOrder->save()){
            $this->controller->commandOut(json_encode($transferOrder->getErrors()));
        }
    }

    /**
     * 提交打款处理
     * @return bool
     */
    public function transmitCommit(){

        $transferOrder = OilJiayoulaTransferOrder::find()->where([
            "status" => "wait"
        ])->orderBy("updated_at DESC")->one();
        if(!$transferOrder)
            return false;

        $t = \Yii::$app->db->beginTransaction();
        try {

            $transferOrder->status     = "transmitting";
            $transferOrder->updated_at = time();
            if(!$transferOrder->save()){
                throw new \Exception(json_encode($transferOrder->getErrors()));
            }

            $transferData = new EfpsTransferData([
                'outTradeNo'      => $transferOrder->order_sn,
                'source_type'     => 'oil_transfer',
                'amount'          => (float)$transferOrder->amount,
                'bankUserName'    => $transferOrder->bankUserName,
                'bankCardNo'      => $transferOrder->bankCardNo,
                'bankName'        => $transferOrder->bankName,
                'bankAccountType' => $transferOrder->bankAccountType,
                'bankNo'          => $transferOrder->bankNo
            ]);

            $res = EfpsTransfer::commit($transferData);

            if($res['code'] != ApiCode::CODE_SUCCESS){
                throw new \Exception($res['msg']);
            }

            $t->commit();

            $this->controller->commandOut("提交处理成功：" . json_encode($transferOrder->getAttributes()));
        }catch (\Exception $e){
            $t->rollBack();
            $this->controller->commandOut($e->getMessage());
        }

        return true;
    }
}