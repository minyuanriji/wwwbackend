<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 支付订单类
 * Author: zal
 * Date: 2020-04-16
 * Time: 10:45
 */


namespace app\core\payment;

use app\models\BaseModel;

class PaymentOrder extends BaseModel
{
    const PAY_TYPE_HUODAO = 'huodao';
    const PAY_TYPE_BALANCE = 'balance';
    const PAY_TYPE_WECHAT = 'wechat';
    const PAY_TYPE_ALIPAY = 'alipay';
    const PAY_TYPE_BAIDU = 'baidu';
    const PAY_TYPE_TOUTIAO = 'toutiao';

    public $orderNo;
    public $amount;
    public $title;
    public $notifyClass;
    public $supportPayTypes;
    public $payType;
    public $user_id;

    public function rules()
    {
        return [
            [['orderNo', 'amount', 'title', 'notifyClass'], 'required',],
            [['orderNo'], 'string', 'max' => 32],
            [['title'], 'string', 'max' => 128],
            [['notifyClass'], 'string', 'max' => 512],
            [['amount'], function ($attribute, $params) {
                if (!is_float($this->amount) && !is_int($this->amount) && !is_double($this->amount)) {
                    $this->addError($attribute, '`amount`必须是数字类型。');
                }
            }],
            [['amount'], 'number', 'min' => 0, 'max' => 100000000],
            [['payType'], 'safe'],
            [['supportPayTypes', 'user_id'], 'safe'],
        ];
    }

    /**
     * PaymentOrder constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        if (!$this->validate()) {
            dd($this->errors);
        }
    }
}
