<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单配置
 * Author: zal
 * Date: 2020-04-18
 * Time: 10:49
 */

namespace app\forms;


use app\models\BaseModel;

/**
 * @property integer $is_sms
 * @property integer $is_mail
 * @property integer $is_print
 * @property integer $is_distribution
 * @property integer $support_share
 */
class OrderConfig extends BaseModel
{
    public $is_sms;
    public $is_print;
    public $is_mail;
    public $is_distribution;
    public $support_share;

    public function rules()
    {
        return [
            [['is_sms', 'is_print', 'is_mail', 'is_distribution', 'support_share'], 'default', 'value' => 0],
            [['is_sms', 'is_print', 'is_mail', 'is_distribution', 'support_share'], 'integer'],
            [['is_sms', 'is_print', 'is_mail', 'is_distribution', 'support_share'], 'in', 'range' => [0, 1]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'is_sms' => '是否开启短信提醒',
            'is_print' => '是否开启小票打印',
            'is_mail' => '是否开启邮件通知',
            'is_distribution' => '是否开启分销',
            'support_share' => '是否支持分销',
        ];
    }

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        if (!$this->validate()) {
            \Yii::error('--order config --' . $this->responseErrorMsg());
        }
    }

    public function setOrder()
    {
        $this->is_distribution = 1;
        $this->is_print = 1;
        $this->is_sms = 1;
        $this->is_mail = 1;
        $this->support_share = 1;
    }
}
