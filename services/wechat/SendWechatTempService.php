<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 发送微信模板
 * Author: xuyaoxiang
 * Date: 2020/10/17
 * Time: 15:13
 */

namespace app\services\wechat;

use app\models\MemberLevel;
use app\models\User;

class SendWechatTempService
{

    /**
     * 会员升级
     * @param $user_id
     * @return array|false
     */
    static public function sendUplevel($user_id)
    {
        $user = User::findOne($user_id);
        if (!$user) {
            return false;
        }

        $member_level = MemberLevel::findOne($user->level);

        if (!$member_level) {
            return false;
        }

        $WechatTemplateService = new WechatTemplateService($user->mall_id);

        $url = "/pages/user/index";

        $h5_url = \Yii::$app->params['web_url'] . "/h5/#" . $url;

        $platform = $WechatTemplateService->getPlatForm();

        $send_data = [
            'first'    => '恭喜您，会员等级提升！',
            'keyword1' => $member_level->name,
            'keyword2' => date("Y-m-d H:i:s", time()),
            'remark'   => "感谢您的支持"
        ];

        return $WechatTemplateService->send($user_id, WechatTemplateService::TEM_KEY['upLevel']['tem_key'], $h5_url, $send_data, $platform, $url);
    }
}