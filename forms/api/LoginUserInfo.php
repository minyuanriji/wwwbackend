<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 登录用户api表单类
 * Author: zal
 * Date: 2020-04-14
 * Time: 14:50
 */

namespace app\forms\api;

use yii\base\Component;

class LoginUserInfo extends Component
{
    public $nickname;
    public $username;
    public $avatar;
    public $platform_user_id;
    public $platform;
}
