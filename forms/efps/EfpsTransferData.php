<?php
namespace app\forms\efps;


use app\models\BaseModel;

class EfpsTransferData extends BaseModel{

    public $outTradeNo;
    public $source_type;
    public $amount;
    public $bankUserName;
    public $bankCardNo;
    public $bankName;
    public $bankAccountType;
    public $bankNo;

    public function rules(){
        return [
            [['outTradeNo', 'source_type', 'amount', 'bankUserName', 'bankCardNo', 'bankName', 'bankAccountType'], 'required'],
            [['bankNo'], 'safe']
        ];
    }

}