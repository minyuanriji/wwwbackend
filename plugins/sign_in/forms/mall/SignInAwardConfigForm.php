<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件-商城后台处理签到奖励机制配置类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:40
 */

namespace app\plugins\sign_in\forms\mall;

use app\plugins\sign_in\forms\BaseModel;
use app\plugins\sign_in\models\SignInAwardConfig;

class SignInAwardConfigForm extends BaseModel
{
    public $day;
    public $number;
    public $type;
    public $status;
    public $coupon_id;

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[SignInAwardConfig::TYPE_SCORE] = ['day', 'number', 'type', 'status'];
        $scenarios[SignInAwardConfig::TYPE_BALANCE] = ['day', 'number', 'type', 'status'];
        $scenarios[SignInAwardConfig::TYPE_COUPON] = ['day', 'number', 'type', 'status','coupon_id'];
        return $scenarios;
    }

    public function rules()
    {
        return [
            [['day', 'type', 'status', 'number'], 'required', 'on' => ['integral', 'balance','coupon']],
            [['day', 'status'], 'integer', 'on' => ['integral', 'balance','coupon']],
            [['type'], 'in', 'range' => ['integral', 'balance','coupon'], 'on' => ['integral', 'balance','coupon']],
            [['number'], 'integer', 'min' => 0, 'on' => 'integral'],
            [['number'], 'number', 'min' => 0, 'on' => 'balance'],
            [['coupon_id'], 'required', 'on' => ['coupon']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'day' => '签到天数',
            'number' => '签到奖励数量',
            'type' => '签到奖励类型',
            'status' => '签到类型'
        ];
    }

    /**
     * 搜索
     * @return $this
     * @throws \Exception
     */
    public function search()
    {
        if (!$this->validate()) {
            throw new \Exception($this->responseErrorMsg($this));
        }
        return $this;
    }
}
