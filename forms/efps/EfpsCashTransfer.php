<?php
namespace app\forms\efps;

use app\models\BaseModel;
use app\models\Cash;

class EfpsCashTransfer extends BaseModel{


    /**
     * 用户提现转账
     * @param Cash $cash
     * @return array
     */
    public static function transfer(Cash $cash){
        $extra = (array)@json_decode($cash->extra, true);

        $transferData = new EfpsTransferData([
            'outTradeNo'      => $cash->order_no,
            'source_type'     => 'user_income_cash',
            'amount'          => (float)$cash->fact_price,
            'bankUserName'    => !empty($extra['name']) ? $extra['name'] : "",
            'bankCardNo'      => !empty($extra['bank_account']) ? $extra['bank_account'] : "",
            'bankName'        => !empty($extra['bank_name']) ? $extra['bank_name'] : "",
            'bankAccountType' => !empty($extra['bankAccountType']) ? $extra['bankAccountType'] : "2"
        ]);

        return EfpsTransfer::execute($transferData);
    }

}