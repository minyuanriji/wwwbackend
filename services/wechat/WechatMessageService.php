<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-22
 * Time: 16:21
 */

namespace app\services\wechat; //注意小写


use app\models\RuleKeyword;
use Yii;
use yii\base\Component;

class WechatMessageService extends Component
{


    private $message;
    /**
     * 群发消息
     *
     * @var array
     */
    protected $sendMethod = [
        'text' => 'sendText',
        'news' => 'sendNews',
        'voice' => 'sendVoice',
        'image' => 'sendImage',
        'video' => 'sendVideo',
        'card' => 'sendCard',
    ];


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-22
     * @Time: 16:49
     * @Note:发送消息
     */
    public function send()
    {


    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-22
     * @Time: 16:50
     * @Note: 发送客服消息
     * @param $openid
     * @param $type
     * @param $data
     */
    public function customer($openid, $type, $data)
    {

    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-22
     * @Time: 16:50
     * @Note:设置消息
     * @param $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-22
     * @Time: 16:50
     * @Note:获取消息
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-22
     * @Time: 16:50
     * @Note:文字消息匹配
     * @return bool
     */
    public function text()
    {
        // 查询用户关键字匹配
        $reply = RuleKeywordService::match($this->message['Content']);
        if ($reply) {
            return $reply;
        }
        return false;
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-22
     * @Time: 16:50
     * @Note:关注匹配回复
     * @return bool
     */
    public function follow()
    {


        return false;
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-22
     * @Time: 16:51
     * @Note:其他匹配回复
     * @return bool
     */
    public function other()
    {
        $message = $this->getMessage();
        $msgType = $message['MsgType'];

        return false;
    }
}