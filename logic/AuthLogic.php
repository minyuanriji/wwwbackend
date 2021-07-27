<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 后台用户业务处理类
 * Author: zal
 * Date: 2020-04-09
 * Time: 14:36
 */

namespace app\logic;

use app\core\Pagination;
use app\forms\admin\Menus;
use app\forms\common\AttachmentForm;
use app\models\Admin;

class AuthLogic
{
    private $superAdminPermissionRoutes = [];
    private $adminPermissionRoutes = [];
    private $notPermissionRoutes = [];

    /**
     * 获取子账号总数
     * @return int
     */
    public static function getChildrenNum()
    {
        $query = Admin::find()->alias('i')->where(['i.admin_type' => 2])->select('i.id');
        $list = Admin::find()->alias('u')->where([
            'u.mall_id' => \Yii::$app->admin->identity->mall_id,
            'u.is_delete' => 0,
        ])
            ->andWhere(['u.id' => $query])
            ->select('u.id')->asArray()->all();

        return count($list);
    }

    /**
     * 获取总管理员可分配的权限列表
     */
    public static function getPermissionsList()
    {
        return [
            'mall' => [
                [
                    'display_name' => '优惠券',
                    'name' => 'coupon',
                ],
                [
                    'display_name' => '专题',
                    'name' => 'topic',
                ],
                [
                    'display_name' => '视频',
                    'name' => 'video',
                ],
                [
                    'display_name' => '版权设置',
                    'name' => 'copyright',
                ],
                [
                    'display_name' => '员工管理',
                    'name' => 'rule_user',
                ],
                [
                    'display_name' => '上传设置',
                    'name' => 'attachment',
                ],
            ],
            'plugins' => \Yii::$app->plugin->list
        ];
    }

    /**
     * 获取子账号管理员不能访问的路由
     */
    public static function getPermissionsRouteList()
    {
        // TODO 此处要使用缓存
        $adminMenus = Menus::getAdminMenus();
        $mallMenus = Menus::getMallMenus();
        $menus = array_merge($adminMenus, $mallMenus);

        $authLogic = new AuthLogic();

        $adminPermissionKeys = \Yii::$app->role->permission;
        $superAdminPermissionKeys = Menus::MALL_SUPER_ADMIN_KEY;

        $authLogic->getMenusRoute($menus, $adminPermissionKeys, $superAdminPermissionKeys);

        return $authLogic->notPermissionRoutes;
    }

    private function getMenusRoute($menus, $adminKeys, $superAdminKeys)
    {
        foreach ($menus as $k => $item) {
            if (isset($item['key']) && !in_array($item['key'], $adminKeys)) {
                $this->notPermissionRoutes[] = $item['route'];
            }

            if (isset($item['key']) && in_array($item['key'], $superAdminKeys)) {
                $this->notPermissionRoutes[] = $item['route'];
            }

            if (isset($item['children'])) {
                $menus[$k]['children'] = $this->getMenusRoute($item['children'], $adminKeys, $superAdminKeys);
            }
        }

        return $menus;
    }

    public static function getAllPermission()
    {
        $permissions = self::getPermissionsList();
        $list = [];
        foreach ($permissions as $key => $permission) {
            if (is_array($permission)) {
                foreach ($permission as $value) {
                    if (isset($value['name'])) {
                        if ($key == 'plugins') {
                            try {
                                $plugin = \Yii::$app->plugin->getPlugin($value['name']);
                            } catch (\Exception $exception) {
                                continue;
                            }
                        }
                        $list[] = $value['name'];
                    }
                }
            }
        }
        return $list;
    }

    /**
     * @return array
     * 二级菜单的所有权限
     */
    public static function getSecondaryPermissionAll()
    {
        $secondaryPermissions = [
            'attachment' => AttachmentForm::getCommon()->getDefaultAuth(),
            'template' => [
                'is_all' => 1,
                'list' => [],
                'use_all' => '1',
                'use_list' => [],
            ]
        ];
        return $secondaryPermissions;
    }

    /**
     * @return array
     * 二级菜单的没有权限
     */
    public static function getSecondaryPermission()
    {
        $secondaryPermissions = [
            'attachment' => [],
            'template' => [
                'is_all' => 0,
                'list' => [],
                'use_all' => '0',
                'use_list' => [],
            ]
        ];
        return $secondaryPermissions;
    }

    /**
     * @return array
     * 二级权限的默认权限
     */
    public static function secondaryDefault()
    {
        $secondaryPermissions = [
            'attachment' => AttachmentForm::getCommon()->getDefaultAuth(),
            'template' => [
                'is_all' => '0',
                'list' => [],
                'use_all' => '0',
                'use_list' => [],
            ]
        ];
        return $secondaryPermissions;
    }

    /**
     * @param $json
     * @return array
     * 兼容新的二级权限
     */
    public static function getSecondaryPermissionList($json)
    {
        $secondaryDefault = AuthLogic::secondaryDefault();
        if ($json) {
            $secondaryPermissions = json_decode($json, true);
            foreach ($secondaryDefault as $key => $item) {
                if (!isset($secondaryPermissions[$key])) {
                    $secondaryPermissions[$key] = $item;
                    continue;
                }
                foreach ($item as $index => $value) {
                    if (!isset($secondaryPermissions[$key][$index])) {
                        $secondaryPermissions[$key][$index] = $value;
                    }
                }
                switch ($key) {
                    case 'template':
                        $list = [];
                        $userList = [];
                        try {
                            $plugin = \Yii::$app->plugin->getPlugin('diy');
                            if (count($secondaryPermissions[$key]['list']) > 0) {
                                $list = $plugin->getMarketListById(array_column($secondaryPermissions[$key]['list'], 'id'));
                            }
                            if (count($secondaryPermissions[$key]['use_list']) > 0) {
                                $userList = $plugin->getLocalListById(array_column($secondaryPermissions[$key]['use_list'], 'id'));
                            }
                        } catch (\Exception $exception) {
                        }
                        $secondaryPermissions[$key]['list'] = $list;
                        $secondaryPermissions[$key]['use_list'] = $userList;
                        break;
                }
            }
        } else {
            $secondaryPermissions = $secondaryDefault;
        }
        return $secondaryPermissions;
    }
}
