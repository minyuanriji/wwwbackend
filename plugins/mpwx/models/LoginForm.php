<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 基础控制器
 * Author: zal
 * Date: 2020-04-14
 * Time: 15:50
 */

namespace app\plugins\mpwx\models;

use app\forms\api\LoginUserInfo;
use app\models\User;
use app\plugins\mpwx\Plugin;

class LoginForm extends \app\forms\api\LoginForm
{
    /**
     * @return LoginUserInfo
     * @throws \Exception
     */
    public function getUserInfo()
    {
        /** @var Plugin $plugin */
        $plugin = new Plugin();
        $postData = \Yii::$app->request->post();
        $rawData = $postData['rawData'];
        $postUserInfo = json_decode($rawData, true);
        $data = $plugin->getWechat()->decryptData(
            $postData['encryptedData'],
            $postData['iv'],
            $postData['code']
        );
        $userInfo = new LoginUserInfo();
        $userInfo->username = $data['openId'];
        $userInfo->nickname = $data['nickName'] ? $data['nickName'] : $postUserInfo['nickName'];
        $userInfo->avatar = $data['avatarUrl'] ? $data['avatarUrl'] : $postUserInfo['avatarUrl'];
        $userInfo->platform_user_id = $data['openId'];
        $userInfo->platform = User::PLATFORM_MP_WX;
        return $userInfo;
    }
}
