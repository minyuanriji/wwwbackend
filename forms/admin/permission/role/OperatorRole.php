<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 操作员角色
 * Author: zal
 * Date: 2020-04-09
 * Time: 17:12
 */

namespace app\forms\admin\permission\role;

use app\helpers\PluginHelper;
use app\logic\AdminLogic;
use app\logic\OptionLogic;
use app\models\Option;
use app\models\Plugin;
use app\models\User;
use app\models\Admin;

class OperatorRole extends BaseRole
{
    public function getName()
    {
        return 'operator';
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
        $userPermissions = AdminLogic::getAdminAllPermissions();
        // 教程管理权限
        $default = [
            'status' => '0',
            'url' => '',
        ];
        $setting = OptionLogic::get(Option::NAME_TUTORIAL, 0, Option::GROUP_ADMIN, $default);
        if ($setting['status'] == 0) {
            foreach ($userPermissions as $key => $userPermission) {
                if ($userPermission == 'mall/tutorial/index') {
                    unset($userPermissions[$key]);
                }
            }
            $userPermissions = array_values($userPermissions);
        }
        $this->permission = $userPermissions;
    }

    public function checkPlugin($plugin)
    {
        if (!($plugin instanceof Plugin)) {
            return false;
        }
        $permission = $this->getPermission();
        $flag = false;
        foreach ($plugin->getMenus() as $menu) {
            if (isset($menu['route']) && in_array($menu['route'], $permission)) {
                $flag = true;
                break;
            }
        }
        if (!$flag) {
            return false;
        }
        return true;
    }

    public function getAccountPermission()
    {
        /* @var User $user */
        $user = Admin::find()
            ->where(['id' => $this->admin->id])
            ->one();
        $config = [
            'admin' =>$this->admin,
            'mall' => $this->mall
        ];
        if ($user->admin_type == 1) {
            $parent = new SuperAdminRole($config);
        } elseif ($user->admin_type == 2 || $user->admin_type == 3) {
            $parent = new AdminRole($config);
        } else {
            throw new \Exception('错误的账户');
        }
        return $parent->permission;
    }

    /*  2021/4/7 改之前版本  */
    public function getAccountPermission2()
    {
        /* @var User $user */
        $user = User::find()->with(['identity'])
            ->where(['id' => $this->mall->user_id])
            ->one();
        $config = [
            'userIdentity' => $user->identity,
            'user' => $user,
            'mall' => $this->mall
        ];
        if ($user->identity->is_super_admin == 1) {
            $parent = new SuperAdminRole($config);
        } elseif ($user->identity->is_admin == 1) {
            $parent = new AdminRole($config);
        } else {
            throw new \Exception('错误的账户');
        }
        return $parent->permission;
    }

    /**
     * @param Plugin $plugin
     * @return mixed
     */
    protected function getPluginIndexRoute($plugin)
    {
        $default = $plugin->getIndexRoute();
        if (in_array($default, $this->permission)) {
            return $default;
        } else {
            foreach ($plugin->getMenus() as $item) {
                if (in_array($item['route'], $this->permission)) {
                    return $item['route'];
                }
            }
            return $default;
        }
    }

    public function getAccount()
    {
        /* @var User $user */
        $user = Admin::find()->with(['adminInfo'])
            ->where(['id' => $this->admin->id])
            ->one();
        $config = [
            'admin'=> $this->admin,
            'user' => $user,
            'mall' => $this->mall
        ];
        if ($user->admin_type == 1) {
            $parent = new SuperAdminRole($config);
        } elseif ($user->admin_type == 2 || $user->admin_type == 3) {
            $parent = new AdminRole($config);
        } else {
            throw new \Exception('错误的账户');
        }
        return $parent;
    }

    /*  2021/4/7 改之前版本  */
    public function getAccount2()
    {
        /* @var User $user */
        $user = User::find()->with(['identity'])
            ->where(['id' => $this->mall->user_id])
            ->one();
        $config = [
            'userIdentity' => $user->identity,
            'user' => $user,
            'mall' => $this->mall
        ];
        if ($user->identity->is_super_admin == 1) {
            $parent = new SuperAdminRole($config);
        } elseif ($user->identity->is_admin == 1) {
            $parent = new AdminRole($config);
        } else {
            throw new \Exception('错误的账户');
        }
        return $parent;
    }

    public function getTemplate()
    {
        $parent = $this->getAccount();
        return $parent->getTemplate();
    }
}
