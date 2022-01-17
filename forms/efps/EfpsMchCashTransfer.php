<?php
namespace app\forms\efps;

use app\models\BaseModel;
use app\plugins\mch\models\MchCash;

class EfpsMchCashTransfer extends BaseModel{

    /**
     * 商家提现提交易票联进行打款处理
     * @param MchCash $mchCash
     * @return array
     */
    public static function commit(MchCash $mchCash){
        $typeData = (array)@json_decode($mchCash->type_data, true);

        $transferData = new EfpsTransferData([
            'outTradeNo'      => $mchCash->order_no,
            'source_type'     => 'mch_cash',
            'amount'          => (float)$mchCash->fact_price,
            'bankUserName'    => !empty($typeData['bankUserName']) ? $typeData['bankUserName'] : "",
            'bankCardNo'      => !empty($typeData['bankCardNo']) ? $typeData['bankCardNo'] : "",
            'bankName'        => !empty($typeData['bankName']) ? $typeData['bankName'] : "",
            'bankAccountType' => !empty($typeData['bankAccountType']) ? $typeData['bankAccountType'] : "",
            "bankNo"          => !empty($typeData['bankNo']) ? $typeData['bankNo'] : ""
        ]);

        return EfpsTransfer::commit($transferData);
    }

}