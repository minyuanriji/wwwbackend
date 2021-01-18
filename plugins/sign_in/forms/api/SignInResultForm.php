<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件接口签到结果表单类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:10
 */

namespace app\plugins\sign_in\forms\api;

use app\core\ApiCode;
use app\plugins\sign_in\forms\common\Common;

class SignInResultForm extends ApiModel
{
    public $queueId;
    public $token;

    public function rules()
    {
        return [
            [['queueId', 'token'], 'required']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        if (!\Yii::$app->queue->isDone($this->queueId)) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'retry' => 1
                ]
            ];
        }
        try {
            $common = Common::getCommon($this->mall);
            $queueData = $common->getQueueData($this->token);
            if ($queueData) {
                throw new \Exception($queueData->data);
            }
            $signIn = $common->getSignInByToken($this->token, $this->user);
            if (!$signIn) {
                throw new \Exception('无效的token');
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'number' => $signIn->number,
                    'type' => $signIn->type,
                    'day' => $signIn->day,
                    'status' => $signIn->status
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
