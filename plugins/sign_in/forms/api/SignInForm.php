<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件接口签到表单类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:10
 */

namespace app\plugins\sign_in\forms\api;

use app\core\ApiCode;
use app\plugins\sign_in\forms\common\Common;
use app\plugins\sign_in\jobs\SignInJob;

class SignInForm extends ApiModel
{
    public $status;
    public $day;

    public function rules()
    {
        return [
            [['status', 'day'], 'required'],
            [['status'], 'in', 'range' => [1, 2, 3]],
            [['day'], 'default', 'value' => 1]
        ];
    }

    public function attributeLabels()
    {
        return [
            'status' => '签到类型',
            'day' => '天数'
        ];
    }



    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $common = Common::getCommon($this->mall);
            $award = $common->getAward($this->status);
            $award->user = $this->user;
            $award->check();
            $token = \Yii::$app->security->generateRandomString();
            $queueId = \Yii::$app->queue->delay(0)->push(new SignInJob([
                'mall' => $this->mall,
                'user' => $this->user,
                'token' => $token,
                'status' => $this->status,
                'day' => $this->day,
            ]));
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'queueId' => $queueId,
                    'token' => $token
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
