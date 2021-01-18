<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 角色
 * Author: zal
 * Date: 2020-04-14
 * Time: 17:32
 */

namespace app\forms\mall\shop\role;

use app\forms\admin\Menus;
use app\forms\admin\permission\menu\MenusForm;
use app\logic\AdminLogic;
use app\logic\OptionLogic;
use app\models\Admin;
use app\models\AdminInfo;
use app\models\BaseModel;
use app\models\Option;
use app\plugins\Plugin;

class RolePermissionForm extends BaseModel
{
    private $adminInfo;

    /**
     * 角色添加/编辑时 所能分配的权限
     * @return mixed
     */
    public function getList()
    {
        $form = new MenusForm();
        $form->isExist = true;
        $res = $form->getMenus('mall');

        if(isset($res['courseMenu']) && !empty($res['courseMenu'])){
            // 教程管理要追加进去
            $res['menus'][] = $res['courseMenu'];
        }
        $this->adminInfo = AdminLogic::getAdminInfo();
        $newMenuList = $this->deleteAdminMenu($res['menus']);

        // 教程管理权限
        $default = [
            'status' => '0',
            'url' => '',
        ];
        $setting = OptionLogic::get(Option::NAME_TUTORIAL, 0, Option::GROUP_ADMIN, $default);
        // 获取插件中心路由
        $adminPermissions = [];
        /** @var Admin $identity */
        $identity = \Yii::$app->admin;
        $admin = Admin::findOne($identity->id);
        if ($admin->admin_type == Admin::ADMIN_TYPE_ADMIN) {
            $adminInfo = AdminInfo::findOne(['admin_id' => \Yii::$app->admin->id]);
            if ($adminInfo) {
                $adminPermissions = json_decode($adminInfo->permissions, true);
            }
        }
        foreach ($newMenuList as $key => &$item) {
            $item = $this->setPluginData($item, $admin, $adminPermissions);
            if (isset($item['key']) && $item['key'] == '')
            if ($setting['status'] == 0) {
                if (isset($item['key']) && $item['key'] == 'course') {
                    unset($newMenuList[$key]);
                }
            }
            if (isset($item['key']) && $item['key'] == 'app-manage') {
                unset($newMenuList[$key]);
            }
        }
        unset($item);

        return array_values($newMenuList);
    }

    /**
     * 去除总管理员独有的菜单，这些菜单子账号和操作员都不能使用
     * @param $list
     * @return mixed
     */
    public function deleteAdminMenu($list)
    {
        foreach ($list as $k => $item) {
            $removePermissions = array_merge(['rule_user'], Menus::MALL_SUPER_ADMIN_KEY);

            if (isset($item['key']) && in_array($item['key'], $removePermissions)) {
                unset($list[$k]);
                continue;
            }

            if (isset($item['children']) && count($item['children']) > 0) {
                $list[$k]['children'] = $this->deleteAdminMenu($item['children']);
            }
        }
        $list = array_values($list);
        return $list;
    }

    private function setPluginData($item, $identity, $adminPermissions)
    {
        if (isset($item['key']) && $item['key'] == 'plugins') {
            $pluginData = $this->setData($identity, $adminPermissions);
            $item['children'] = $pluginData;
        }
        if (isset($item['children'])) {
            foreach ($item['children'] as $key => $child) {
                $item['children'][$key] = $this->setPluginData($child, $identity, $adminPermissions);
            }
        }
        return $item;
    }

    /**
     *
     * @param Admin $identity
     * @param $adminPermissions
     * @return array
     */
    private function setData($identity, $adminPermissions)
    {
        $plugins = \Yii::$app->role->getMallRole();
        $pluginMenus = [];
        foreach ($plugins->permission as $plugin) {
            // 子账号需判断是否
            if ($identity->admin_type != Admin::ADMIN_TYPE_SUPER && !in_array($plugin, $adminPermissions)) {
                continue;
            }
            $PluginClass = 'app\\plugins\\' . $plugin . '\\Plugin';
            /** @var Plugin $pluginObject */
            if (!class_exists($PluginClass)) {
                continue;
            }
            $object = new $PluginClass();
            if (method_exists($object, 'getMenus')) {
                $menus = $object->getMenus();
                if ($menus) {
                    $newMenus = [
                        'name' => $object->getDisplayName(),
                        'icon' => '',
                        'children' => $menus,
                        'route' => $menus[0]['route'],
                    ];
                    $pluginMenus[] = $newMenus;
                }
            }
        }
        return $pluginMenus;
    }
}
