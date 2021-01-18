<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 邮件设置
 * Author: zal
 * Date: 2020-04-23
 * Time: 17:39
 */

namespace app\forms\mall\setting;

use app\core\ApiCode;
use app\core\mail\SendMail;
use app\models\BaseModel;
use app\models\MailSetting;

/**
 * @property MailSetting $model
 */
class MailSettingForm extends BaseModel
{
    public $status;

    public $send_mail;
    public $send_pwd;
    public $send_name;
    public $receive_mail;
    public $test;

    public $model;

    public function rules()
    {
        return [
            [['status', 'test'], 'integer'],
            ['status', 'default', 'value' => 0],
            [['send_mail', 'send_pwd', 'send_name'], 'string'],
            [['send_mail', 'send_pwd', 'send_name'], 'trim'],
            [['receive_mail'], 'safe']
        ];
    }

    /**
     * 保存
     * @return array
     */
    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        if ($this->status == 1) {
            if (!($this->send_mail && $this->send_pwd && $this->send_name && $this->receive_mail)) {
                return [
                    'code' => 1,
                    'msg' => '请填写信息'
                ];
            }
        }
        $this->receive_mail = $this->receive_mail ? implode(',', $this->receive_mail) : '';
        $this->model->attributes = $this->attributes;
        if ($this->test) {
            return $this->test();
        }
        if ($this->model->isNewRecord) {
            $this->model->is_delete = 0;
            $this->model->mall_id = \Yii::$app->mall->id;
            $this->model->mch_id = \Yii::$app->admin->identity->mch_id;
        }
        if ($this->model->save()) {
            return [
                'code' => 0,
                'msg' => '保存成功'
            ];
        } else {
            return $this->responseErrorInfo($this->model);
        }
    }

    public function test()
    {
        try {
            $mailer = new SendMail();
            $mailer->mall = \Yii::$app->mall;
            $mailer->mailSetting = $this->model;
            $mailer->test();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '发送成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '发送失败，请检查发件人邮箱、授权码及收件人邮箱是否正确',
                'data' => $exception
            ];
        }
    }
}
