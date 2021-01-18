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
use app\models\Admin;

class SuperAdminRole extends BaseRole
{
    public static $superAdmin;
    public $isSuperAdmin = true;

    public function getName()
    {
        return 'super_admin';
    }

    public function deleteRoleMenu($menu)
    {
        if (isset($menu['key']) && in_array($menu['key'], ['attachment'])) {
            return $this->special($menu);
        } else {
            return false;
        }
    }

    public function setPermission()
    {
        $this->permission = AuthLogic::getAllPermission();
    }

    public $showDetail = true;

    public function checkPlugin($plugin)
    {
        return true;
    }

    public function getNotPluginList()
    {
        // 1.获取远程已购买插件列表
//        try {
//            $cloudData = \Yii::$app->cloud->plugin->getList();
//        } catch (CloudException $exception) {
//            $cloudData = [
//                'list' => [],
//            ];
//        }

        // 2.获取本地插件代码列表
        try {
            $localSrcList = \Yii::$app->plugin->scanPluginList();
        } catch (\Exception $exception) {
            $localSrcList = [];
        }

        // 3.合并1/2的插件列表
        $pluginList = [];
//        foreach ($cloudData['list'] as $cloudItem) {
//            $pluginList[] = [
//                'name' => $cloudItem['name'],
//                'display_name' => $cloudItem['display_name'],
//                'pic_url' => $cloudItem['pic_url'],
//            ];
//        }
        foreach ($localSrcList as $localItem) {
            $inArray = false;
            foreach ($pluginList as $item) {
                if ($item['name'] === $localItem->getName()) {
                    $inArray = true;
                    break;
                }
            }
            if ($inArray) {
                continue;
            }
            $pluginList[] = [
                'name' => $localItem->getName(),
                'display_name' => $localItem->getDisplayName(),
                'pic_url' => $localItem->getIconUrl(),
            ];
        }

        // 4.获取数据库已安装插件列表
        $installedList = \Yii::$app->plugin->getList();

        // 5.排除掉数据库已安装的插件
        foreach ($installedList as $installedItem) {
            foreach ($pluginList as $i => $item) {
                if ($installedItem->name === $item['name']) {
                    unset($pluginList[$i]);
                    break;
                }
            }
        }
        $pluginList = array_values($pluginList);
        return $pluginList;
    }

    /**
     * @param $menu
     * @return bool
     * 特殊的菜单序要特殊处理
     */
    private function special($menu)
    {
        try {
            $mall = \Yii::$app->mall;
            if ($mall->user_id == 1) {
                return false;
            }
            $user = \Yii::$app->mall->user;
            $permission = json_decode($user->adminInfo->permissions, true);
            if (!in_array('attachment', $permission)) {
                return true;
            }
            return false;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @return UserIdentity|null
     * 获取总管理员账号
     */
    public static function getSuperAdmin()
    {
        if (self::$superAdmin) {
            return self::$superAdmin;
        }
        self::$superAdmin = Admin::findOne(['admin_type' => 1]);
        return self::$superAdmin;
    }

    public function getSecondaryPermission()
    {
        return AuthLogic::getSecondaryPermissionAll();
    }
}
