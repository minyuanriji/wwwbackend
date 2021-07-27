<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 用户缓存类
 * Author: zal
 * Date: 2020-04-24
 * Time: 16:55
 */

namespace app\component\caches;

class UserCache extends BaseCache
{
    public function addUserInfoById($id, $value)
    {
        return $this->setValue('user_info'.$id, $value);
    }

    public function getUserInfoById($id)
    {
        return $this->getValue('user_info'.$id);
    }

    public function delUserInfoById($id)
    {
        return $this->deleteValue('user_info'.$id);
    }
}