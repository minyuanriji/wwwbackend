<?php
namespace app\forms\efps;


use app\models\BaseModel;

class EfpsTransferData extends BaseModel{

    public $outTradeNo;
    public $amount;
    public $bankUserName;
    public $bankCardNo;
    public $bankName;
    public $bankAccountType;

    public function rules(){
        return [
            [['outTradeNo', 'amount', 'bankUserName', 'bankCardNo', 'bankName', 'bankAccountType'], 'required']
        ];
    }

}