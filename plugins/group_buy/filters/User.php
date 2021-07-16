<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/9/4
 * Time: 17:23
 */

namespace app\plugins\group_buy\filters;

use app\forms\mall\shop\UserCenterForm;
use app\models\User as ModelUser;

class User
{
    /**
     * 获取用户默认头像
     * @return mixed
     */
    static function getDefaultAvatar()
    {

        $user_center_form = new UserCenterForm();
        $default          = $user_center_form->getDefault();
        $member_pic_url   = $default['member_pic_url'];

        return $member_pic_url;
    }

    static function filterItem($item)
    {
        $member_pic_url = self::getDefaultAvatar();

        return [
            'id'         => $item['id'],
            'username'   => $item['username'],
            'nickname'   => $item['nickname'],
            'avatar_url' => !empty($item['avatar_url']) ? $item['avatar_url'] : $member_pic_url,
        ];
    }

    function getItemByUserId($user_id)
    {
        $member_pic_url = self::getDefaultAvatar();

        $user = ModelUser::findOne($user_id);

        if ($user) {
            $user = $user->toArray();
        } else {
            return [];
        }

        return [
            'id'         => $user['id'],
            'username'   => $user['username'],
            'nickname'   => $user['nickname'],
            'avatar_url' => !empty($user['avatar_url']) ? $user['avatar_url'] : $member_pic_url,
        ];
    }
}