<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 模板消息发送
 * Author: zal
 * Date: 2020-04-14
 * Time: 14:50
 */

namespace app\forms\common\template;


use app\component\jobs\TemplateSendJob;
use app\models\BaseModel;
use app\models\User;
use app\models\UserInfo;

class TemplateSend extends BaseModel
{
    public $user;
    public $page;
    public $data;
    public $templateTpl;
    public $templateId;
    public $titleStyle;
    public $platform;
    public $dataKey;

    /* @var TemplateSender */
    public $sender;

    /**
     * @return array
     * @throws \Exception
     */
    public function sendTemplate()
    {
        if (!is_array($this->user)) {
            if (isset($this->user) && $this->user instanceof User) {
                $this->platform = $this->user->platform;
            } else {
                throw new \Exception('参数错误，缺少有效的参数user');
            }
        } else {
            $this->platform = $this->user[0]->platform;
        }
        $token = \Yii::$app->security->generateRandomString(32);
        $templateMsg['page'] = $this->page;
        $templateMsg['data'] = $this->data;
        $templateMsg['templateTpl'] = $this->templateTpl;
        $templateMsg['templateId'] = $this->templateId;
        $templateMsg['user'] = $this->user;
        $templateMsg['token'] = $token;
        $templateMsg['dataKey'] = $this->dataKey;
        $templateMsg['titleStyle'] = $this->titleStyle;
        $queueId = \Yii::$app->queue->delay(0)->push(new TemplateSendJob($templateMsg));
        return [
            'queueId' => $queueId,
            'token' => $token
        ];
    }
}
