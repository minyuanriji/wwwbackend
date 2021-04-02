<?php
namespace app\component\jobs;

use app\component\efps\Efps;
use app\component\lib\LockTools;
use app\core\ApiCode;
use app\mch\forms\mch\MchAccountModifyForm;
use app\models\EfpsTransferOrder;
use app\plugins\mch\models\MchCash;
use yii\base\Component;
use yii\queue\JobInterface;

class EfpsTransferJob extends Component implements JobInterface{

    public function execute($queue){
        //$lock_tools = new LockTools();
        //$lock_name = 'lock:EfpsTransferJob';

        //if(!$lock_tools->lock($lock_name)){
        //    echo "LOCK";
        //    return;
        //}

        $mchCash = MchCash::find()->where([
            "type"            => "efps_bank",
            "status"          => 1,
            "transfer_status" => 0
        ])->orderBy("updated_at ASC")->one();
        if(!$mchCash) return;

        $mchCash->updated_at = time();
        $mchCash->save();

        $exceptionForceCommit = false;
        $t = \Yii::$app->getDb()->beginTransaction();
        try {
            $transferOrder = EfpsTransferOrder::findOne(["outTradeNo" => $mchCash->order_no]);
            if(!$transferOrder){
                $typeData = (array)@json_decode($mchCash->type_data, true);
                $transferOrder = new EfpsTransferOrder([
                    "status"          => 0,
                    "outTradeNo"      => $mchCash->order_no,
                    "customerCode"    => \Yii::$app->efps->getCustomerCode(),
                    "notifyUrl"       => "http://",
                    "amount"          => $mchCash->fact_price,
                    "bankUserName"    => !empty($typeData['bankUserName']) ? $typeData['bankUserName'] : "",
                    "bankCardNo"      => !empty($typeData['bankCardNo']) ? $typeData['bankCardNo'] : "",
                    "bankName"        => !empty($typeData['bankName']) ? $typeData['bankName'] : "",
                    "bankAccountType" => !empty($typeData['bankAccountType']) ? $typeData['bankAccountType'] : "",
                    "created_at"      => time(),
                    "updated_at"      => time()
                ]);
            }

            if($transferOrder->status == 0){ //待提交
                $transferOrder->status = 1;
                if(!$transferOrder->save()){
                    throw new \Exception(json_encode($transferOrder->getErrors()));
                }

                $res = \Yii::$app->efps->withdrawalToCard([
                    "customerCode"    => \Yii::$app->efps->getCustomerCode(),
                    "outTradeNo"      => $transferOrder->outTradeNo,
                    "notifyUrl"       => $transferOrder->notifyUrl,
                    "amount"          => $transferOrder->amount * 100,
                    "bankUserName"    => $transferOrder->bankUserName,
                    "bankCardNo"      => $transferOrder->bankCardNo,
                    "bankName"        => $transferOrder->bankName,
                    "bankAccountType" => $transferOrder->bankAccountType
                ]);

                if($res['code'] != Efps::CODE_SUCCESS){
                    $exceptionForceCommit = true;
                    $transferOrder->remark = $res['msg'];
                    if($res['data']['returnCode'] == "09109"){ //重复请求
                        $transferOrder->status = 1;
                    }else{
                        $transferOrder->status = 3;
                    }
                    $transferOrder->save();
                }
            }elseif($transferOrder->status == 1){ //已提交
                $res = \Yii::$app->efps->withdrawalToCardQuery([
                    "customerCode" => \Yii::$app->efps->getCustomerCode(),
                    "outTradeNo"   => $transferOrder->outTradeNo
                ]);
                if($res['code'] != Efps::CODE_SUCCESS){
                    throw new \Exception($res['msg']);
                }

                $transferOrder->updated_at = time();
                if($res['data']['returnCode'] == "0000" && $res['data']['payState'] == "00"){ //支付成功
                    $transferOrder->status = 2;
                }else{
                    $transferOrder->status = 3;
                    $transferOrder->remark = $res['data']['returnMsg'];
                }
                if(!$transferOrder->save()){
                    throw new \Exception(json_encode($transferOrder->getErrors()));
                }
            }elseif($transferOrder->status == 2) { //成功
                $mchCash->transfer_status = 1;
                $mchCash->save();
            }else{ //失败
                $mchCash->transfer_status = 2;
                if(!$mchCash->save()){
                    throw new \Exception(json_encode($mchCash->getErrors()));
                }

                //退还余额
                $form = new MchAccountModifyForm([
                    'mall_id' => $mchCash->mall_id,
                    'mch_id'  => $mchCash->mch_id,
                    'type'    => 1,
                    'money'   => $mchCash->money,
                    'desc'    => "提现失败，返还帐户"
                ]);
                $res = $form->save();
                if($res['code'] != ApiCode::CODE_SUCCESS){
                    throw new \Exception($res['msg']);
                }
            }
            $t->commit();
        }catch (\Exception $e){
            if($exceptionForceCommit){
                $t->commit();
            }else{
                $t->rollBack();
            }
        }

        //$lock_tools->unlock($lock_name);
    }
}