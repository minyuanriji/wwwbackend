<?php
namespace app\forms\efps;


use app\component\efps\Efps;
use app\core\ApiCode;
use app\models\BaseModel;
use app\models\EfpsTransferOrder;

class EfpsTransfer extends BaseModel{

    /**
     * 提交打款处理
     * @param $outTradeNo
     * @return array
     */
    public static function commit(EfpsTransferData $transferData){

        try {
            if(!$transferData->validate()){
                throw new \Exception(json_encode($transferData->getErrors()));
            }

            $transferOrder = EfpsTransferOrder::findOne(["outTradeNo" => $transferData->outTradeNo]);
            if(!$transferOrder){
                $transferOrder = new EfpsTransferOrder([
                    "status"          => 0,
                    "outTradeNo"      => $transferData->outTradeNo,
                    "source_type"     => $transferData->source_type,
                    "customerCode"    => \Yii::$app->efps->getCustomerCode(),
                    "notifyUrl"       => "http://",
                    "amount"          => $transferData->amount,
                    "bankUserName"    => $transferData->bankUserName,
                    "bankCardNo"      => $transferData->bankCardNo,
                    "bankName"        => $transferData->bankName,
                    "bankAccountType" => $transferData->bankAccountType,
                    "created_at"      => time()
                ]);
            }

            if($transferOrder->status == 0){
                $transferOrder->status = 1;
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
                    $transferOrder->status = 3;
                    $transferOrder->remark = "提交打款处理失败";
                }else{
                    if($res['data']['returnCode'] == "0000"){
                        $transferOrder->status = 1;
                        $transferOrder->remark = "提交打款处理成功";
                    }else{
                        $transferOrder->status = 3;
                        $transferOrder->remark = "提交打款处理失败";
                    }
                }

                $transferOrder->request_text = !empty($res['json_str']) ? $res['json_str'] : "";
                $transferOrder->resonse_text = !empty($res['res_text']) ? $res['res_text'] : "";
            }

            $transferOrder->updated_at = time();
            if(!$transferOrder->save()){
                throw new \Exception(json_encode($transferOrder->getErrors()));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => "操作成功"
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }



    }

    /**
     * 查询打款状态
     * @param $outTradeNo
     * @return array
     */
    public static function query($outTradeNo){
        $res = \Yii::$app->efps->withdrawalToCardQuery([
            "customerCode" => \Yii::$app->efps->getCustomerCode(),
            "outTradeNo"   => $outTradeNo
        ]);

        if($res['code'] == Efps::CODE_SUCCESS && $res['data']['returnCode'] == "0000" && $res['data']['payState'] == "00"){
            $res['code'] = ApiCode::CODE_SUCCESS;
            $res['msg']  = '打款成功';
        }else{
            $res['code'] = ApiCode::CODE_FAIL;
            $res['msg']  = "打款失败：" . $res['data']['returnMsg'];
        }

        return $res;
    }

    public static function execute(EfpsTransferData $transferData){

        if(!$transferData->validate()){
            throw new \Exception(json_encode($transferData->getErrors()));
        }

        $transferOrder = EfpsTransferOrder::findOne(["outTradeNo" => $transferData->outTradeNo]);

        if(!$transferOrder){
            $transferOrder = new EfpsTransferOrder([
                "status"          => 0,
                "outTradeNo"      => $transferData->outTradeNo,
                "source_type"     => $transferData->source_type,
                "customerCode"    => \Yii::$app->efps->getCustomerCode(),
                "notifyUrl"       => "http://",
                "amount"          => $transferData->amount,
                "bankUserName"    => $transferData->bankUserName,
                "bankCardNo"      => $transferData->bankCardNo,
                "bankName"        => $transferData->bankName,
                "bankAccountType" => $transferData->bankAccountType,
                "created_at"      => time(),
                "updated_at"      => time()
            ]);
            if(!$transferOrder->save()){
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg'  => json_encode($transferOrder->getErrors())
                ];
            }
        }

        try {
            $isTransmitting = 1;
            if($transferOrder->status == 2){ //成功
                $res['code'] = ApiCode::CODE_SUCCESS;
                $res['msg']  = '打款成功';
            }else{
                if($transferOrder->status == 0){ //提交
                    $transferOrder->status = 1;
                    if($transferOrder->save()){
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
                            throw new \Exception($res['msg']);
                        }

                        if($res['data']['returnCode'] == "0000" && $res['data']['payResult'] == "03"){
                            $isTransmitting = 1;
                            $transferOrder->status = 2;
                            $transferOrder->remark = "提交打款成功";
                        }

                        $transferOrder->request_text = !empty($res['json_str']) ? $res['json_str'] : "";
                        $transferOrder->resonse_text = !empty($res['res_text']) ? $res['res_text'] : "";
                    }
                }

                if($transferOrder->status != 2){

                    //查询易票联结果
                    $res = \Yii::$app->efps->withdrawalToCardQuery([
                        "customerCode" => \Yii::$app->efps->getCustomerCode(),
                        "outTradeNo"   => $transferOrder->outTradeNo
                    ]);

                    if($res['code'] == Efps::CODE_SUCCESS && $res['data']['returnCode'] == "0000" && $res['data']['payState'] == "00"){
                        $isTransmitting = 0;
                        $transferOrder->status = 2;
                        $res['code'] = ApiCode::CODE_SUCCESS;
                        $res['msg']  = '打款成功';
                    }else{
                        $transferOrder->remark = $res['data']['returnMsg'];
                        $res['code'] = ApiCode::CODE_FAIL;
                        $res['msg']  = "打款失败：" . $res['data']['returnMsg'];
                    }
                }else{
                    $res['code'] = ApiCode::CODE_SUCCESS;
                    $res['msg']  = '打款成功';
                }

                $transferOrder->updated_at = time();
                $transferOrder->save();
            }

            $res['is_transmitting'] = $isTransmitting;

            return $res;
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}