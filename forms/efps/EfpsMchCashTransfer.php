<?php
namespace app\forms\efps;

use app\models\BaseModel;
use app\plugins\mch\models\MchCash;

class EfpsMchCashTransfer extends BaseModel{

    /**
     * 商家提现转账
     * @param MchCash $mchCash
     * @return array
     */
    public static function transfer(MchCash $mchCash){

        $typeData = (array)@json_decode($mchCash->type_data, true);

        $transferData = new EfpsTransferData([
            'outTradeNo'      => $mchCash->order_no,
            'source_type'     => 'mch_cash',
            'amount'          => (float)$mchCash->fact_price,
            'bankUserName'    => !empty($typeData['bankUserName']) ? $typeData['bankUserName'] : "",
            'bankCardNo'      => !empty($typeData['bankCardNo']) ? $typeData['bankCardNo'] : "",
            'bankName'        => !empty($typeData['bankName']) ? $typeData['bankName'] : "",
            'bankAccountType' => !empty($typeData['bankAccountType']) ? $typeData['bankAccountType'] : ""
        ]);

        return EfpsTransfer::execute($transferData);
    }
}