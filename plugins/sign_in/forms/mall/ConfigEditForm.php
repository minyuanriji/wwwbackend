<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件-商城后台配置操作
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:40
 */

namespace app\plugins\sign_in\forms\mall;

use app\core\ApiCode;
use app\plugins\sign_in\forms\BaseModel;
use app\plugins\sign_in\forms\common\Common;
use app\plugins\sign_in\models\SignInAwardConfig;

class ConfigEditForm extends BaseModel
{
    public $status;
    public $is_remind;
    public $time;
    public $normal_type;
    public $normal;
    public $continue;
    public $total;
    public $rule;
    public $continue_type;
    public $coupon_num;
    public $coupon;
    public $name;
    public $push_url;

    public function rules()
    {
        $type = ['integral', 'balance','coupon'];
        return [
            [['status', 'is_remind', 'continue_type','coupon_num','coupon'], 'integer'],
            [['continue', 'total'], function ($attr) use ($type) {
                $dayArr = [];
                if (is_array($this->$attr)) {
                    foreach ($this->$attr as $item) {
                        if (in_array($item['day'], $dayArr)) {
                            $this->addError($attr, "{$this->getAttributeLabel($attr)}天数不能相同");
                        }
                        if (!in_array($item['type'], $type)) {
                            $this->addError($attr, "{$this->getAttributeLabel($attr)}奖励类型不合法");
                        }
                        if ($item['number'] < 0) {
                            $this->addError($attr, "{$this->getAttributeLabel($attr)}奖励数量必须大于0");
                        }
                        if ($item['type'] == 'integral') {
                            $item['number'] = round($item['number'], 2);
                            if (!is_numeric($item['number']) || strpos($item['number'], ".") !== false) {
                                $this->addError($attr, "{$this->getAttributeLabel($attr)}奖励类型为积分时，数量必须为整数");
                            }
                        }
                        $dayArr[] = $item['day'];
                    }
                }
            }],
            [['rule', 'time','name','push_url'], 'string'],
            [['normal_type'], 'in', 'range' => $type],
            [['time'], 'default', 'value' => '00:00:00'],
            [['normal'], 'number', 'min' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'status' => '签到是否开启',
            'is_remind' => '签到是否提醒',
            'normal' => '普通签到赠送数量',
            'normal_type' => '普通签到奖励类型',
            'continue_type' => '连续签到周期',
            'continue' => '连续签到设置',
            'total' => '累计签到设置',
            'rule' => '签到规则',
            'time' => '提醒时间'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            //签到奖励类型
            $type = SignInAwardConfig::TYPE_BALANCE;
            if ($this->normal_type == 'integral') {
                $type = SignInAwardConfig::TYPE_SCORE;
                $this->normal = round($this->normal, 2);
                if (!is_numeric($this->normal) || strpos($this->normal, ".") !== false) {
                    throw new \Exception('普通签到奖励类型为积分时，奖励数量必须是整数');
                }
            }
            $common = Common::getCommon($this->mall);
            $config = $common->getConfig();
            $newList = [];
            //默认积分

            if (!empty($this->normal)) {
                if (!is_numeric($this->normal) || $this->normal <= 0)throw new \Exception('请检查积分数量1');
                $newList[] = [
                    'number' => $this->normal,
                    'day' => 1,
                    'type' => SignInAwardConfig::TYPE_SCORE,
                    'status' => SignInAwardConfig::TYPE_SCORE,
                ];
            }
            //默认优惠券
            if (!empty($this->coupon)) {
                if (empty($this->coupon_num) || !is_int($this->coupon_num) || $this->coupon_num <= 0){
                    throw new \Exception('请检查优惠券数量');
                }
                $newList[] = [
                    'number' => $this->coupon_num,
                    'day' => 1,
                    'type' => SignInAwardConfig::TYPE_COUPON,
                    'coupon_id' => $this->coupon,
                    'status' => 1,
                ];

            }
            if ($this->continue) {
                foreach ($this->continue as $item) {

                    if (empty($item['number']) || !is_int($item['number']) || $item['number'] <= 0){
                        throw new \Exception('请检查优惠券数量');
                    }

                    if (!is_int($item['day']) || $item['day'] <= 0)throw new \Exception('请检查签到天数');
                    $newList[] = [
                        'number' => $item['number'],
                        'day' => $item['day'],
                        'type' => SignInAwardConfig::TYPE_COUPON,
                        'coupon_id' => $item['coupon_id'],
                        'status' => 2,
                    ];
                }
            }
            if ($this->total) {
                foreach ($this->total as $item) {

                    if (!is_int($item['number']) || $item['number'] <= 0)throw new \Exception('请检查积分数量');
                    if (!is_int($item['day']) || $item['day'] <= 0)throw new \Exception('请检查签到天数');
                    $newList[] = [
                        'number' => $item['number'],
                        'day' => $item['day'],
                        'type' => SignInAwardConfig::TYPE_SCORE,
                        'status' => 2,
                    ];
                }
            }
            $common->addAwardConfig($newList);
            $common->addConfig($config, $this->attributes);

            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage(),
            ];
        }
    }
}
