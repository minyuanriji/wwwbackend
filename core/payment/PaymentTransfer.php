<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 支付交易类
 * Author: zal
 * Date: 2020-04-16
 * Time: 10:45
 */

namespace app\core\payment;

use app\models\BaseModel;
use app\models\User;

/**
 * @property User $user
 */
class PaymentTransfer extends BaseModel
{
    const TRANSFER_TYPE_WECHAT = 'wechat';
    const TRANSFER_TYPE_MP_WX = 'mp-wx';
    const TRANSFER_TYPE_MP_ALI = 'mp-ali';
    const TRANSFER_TYPE_MP_BD = 'mp-bd';
    const TRANSFER_TYPE_MP_TT = 'mp-tt';

    public $orderNo;
    public $amount;
    public $user;
    public $title;
    public $transferType;

    public function rules()
    {
        return [
            [['orderNo', 'amount', 'user', 'title', 'transferType'], 'required'],
            ['orderNo', 'string', 'max' => 32],
            [['title'], 'string', 'max' => 128],
            [['amount'], function ($attribute, $params) {
                if (!is_float($this->amount) && !is_int($this->amount) && !is_double($this->amount)) {
                    $this->addError($attribute, '`amount`必须是数字类型。');
                }
            }],
            [['amount'], 'number', 'min' => 0.01, 'max' => 100000000],
            [['transferType'], 'in', 'range' => [User::PLATFORM_WECHAT, User::PLATFORM_H5, User::PLATFORM_MP_WX, User::PLATFORM_MP_ALI, User::PLATFORM_MP_BD, User::PLATFORM_MP_TT]],
            ['user', function ($attribute, $param) {
                if (!$this->user instanceof User) {
                    $this->addError($attribute, 'user必须是app\\models\User的对象');
                }
            }]
        ];
    }

    /**
     * PaymentTransfer constructor.
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
