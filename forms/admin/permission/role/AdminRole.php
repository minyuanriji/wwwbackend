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

use app\logic\AuthLogic;
use app\logic\OptionLogic;
use app\models\Option;
use app\models\AdminInfo;
use yii\composer\Plugin;

class AdminRole extends BaseRole
{
    public function getName()
    {
        return 'admin';
    }

    public function deleteRoleMenu($menu)
    {
        $permission = $this->getPermission();
        if (isset($menu['key']) && !in_array($menu['key'], $permission)) {
            return true;
        }
        return false;
    }

    private $allow = ['statistics', 'cache_manage'];

    public function setPermission()
    {
        // 总账户授予单独子账户的权限
        $permission = $this->getPluginPermission();
        // 教程管理权限
        $default = [
            'status' => '0',
            'url' => '',
        ];
        $setting = OptionLogic::get(Option::NAME_TUTORIAL, 0, Option::GROUP_ADMIN, $default);
        if ($setting['status'] == 1) {
            array_push($permission, 'course');
        }
        // 插件中心权限
        if (count($this->getInstalledPluginList()) > 0) {
            array_push($permission, 'plugins');
        }
        // 独立版的子账号有该菜单 微擎版没有
        if (!is_we7()) {
            $this->allow[] = 'small_procedure';
        }
        // 所有子账户公有的权限
        $permission = array_merge($permission, $this->allow);
        $this->permission = $permission;
    }

    // 插件相关权限
    public $pluginPermission;

    public function getPluginPermission()
    {
        if ($this->pluginPermission) {
            return $this->pluginPermission;
        }
        /* @var AdminInfo $adminInfo */
        $adminInfo = AdminInfo::find()->where([
            'admin_id' => $this->admin->id,
            'is_delete' => 0
        ])->one();
        if ($adminInfo) {
            $flag = 2;
        } else {
            $flag = 1;
        }
        $permission = \Yii::$app->branch->childPermission($flag);
        $this->pluginPermission = $permission;
        return $permission;
    }

    public function checkPlugin($plugin)
    {
        if (!($plugin instanceof Plugin)) {
            return false;
        }
        $permission = $this->getPluginPermission();
        if (is_array($permission) && !in_array($plugin->getName(), $permission)) {
            return false;
        }
        return true;
    }

    public function getSecondaryPermission()
    {
        $secondaryPermission = \Yii::$app->branch->getSecondaryPermission($this->user->adminInfo);
        if (isset($secondaryPermission['template'])) {
            if ($secondaryPermission['template']['is_all'] == 0) {
                if (isset($secondaryPermission['template']['list']) && !empty($secondaryPermission['template']['list'])) {
                    $secondaryPermission['template']['list'] = array_column($secondaryPermission['template']['list'], 'id');
                } else {
                    $secondaryPermission['template']['list'] = [];
                }
            }
            if (isset($secondaryPermission['template']['use_list']) && !empty($secondaryPermission['template']['use_list'])) {
                $secondaryPermission['template']['use_list'] = array_column($secondaryPermission['template']['use_list'], 'id');
            } else {
                $secondaryPermission['template']['use_list'] = [];
            }
        } else {
            $default = AuthLogic::secondaryDefault();
            $secondaryPermission['template'] = $default['template'];
        }
        return $secondaryPermission;
    }
}
