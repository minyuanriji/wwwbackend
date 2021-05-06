<?php
namespace app\forms\efps;


use app\component\efps\Efps;
use app\core\ApiCode;
use app\models\BaseModel;
use app\models\EfpsTransferOrder;
use app\plugins\mch\models\MchCash;

class EfpsMchCashTransfer extends BaseModel{

    public static function transfer(MchCash $mchCash){

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
                    }
                }

                //查询易票联结果
                $res = \Yii::$app->efps->withdrawalToCardQuery([
                    "customerCode" => \Yii::$app->efps->getCustomerCode(),
                    "outTradeNo"   => $transferOrder->outTradeNo
                ]);

                if($res['code'] == Efps::CODE_SUCCESS && $res['data']['returnCode'] == "0000" && $res['data']['payState'] == "00"){
                    $transferOrder->status = 2;
                    $res['code'] = ApiCode::CODE_SUCCESS;
                    $res['msg']  = '打款成功';
                }else{
                    $transferOrder->remark = $res['data']['returnMsg'];
                    $res['code'] = ApiCode::CODE_FAIL;
                    $res['msg']  = $res['data']['returnMsg'];
                }

                $transferOrder->updated_at = time();
                $transferOrder->save();
            }

            $t->commit();

            return $res;
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }
}