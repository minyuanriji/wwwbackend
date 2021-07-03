<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 管理员后台设置
 * Author: zal
 * Date: 2020-04-09
 * Time: 17:12
 */

namespace app\forms\admin\permission\role;

use app\logic\AdminLogic;

class MchRole extends BaseRole
{
    public function getName()
    {
        return 'mch';
    }

    public function deleteRoleMenu($menu)
    {
        if (isset($menu['route']) && !in_array($menu['route'], $this->getPermission())) {
            return true;
        }
        return false;
    }

    public function setPermission()
    {
        $this->permission = AdminLogic::getMchPermissions();
    }

    public function getAccountPermission()
    {
        return false;
    }

    public function getAccount()
    {
        return false;
    }
}
