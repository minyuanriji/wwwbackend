<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 基础角色
 * Author: zal
 * Date: 2020-04-09
 * Time: 17:12
 */

namespace app\forms\admin\permission\role;


use app\forms\admin\Menus;
use app\logic\AuthLogic;
use app\models\Admin;
use app\models\BaseModel;
use app\models\Mall;

/**
 * @property array $permission
 * @property string $name
 * @property Plugin[] $pluginList
 * @property Admin $admin
 * @property Mall $mall
 * @property boolean isSuperAdmin 是否是超级管理员
 */
abstract class BaseRole extends BaseModel
{
    protected $permission;
    protected $name;
    protected $pluginList;
    public $adminInfo;
    public $admin;
    public $mall;
    public $user;
    public $isSuperAdmin = false;

    public function init()
    {
        parent::init();
        $this->setPermission();
    }

    /**
     * @return mixed
     * 获取角色身份
     */
    abstract public function getName();

    /**
     * @param $menu
     * @return boolean true--删除菜单|false--保留菜单
     * @throws \Exception
     * 只删除非本角色权限内的菜单
     */
    abstract public function deleteRoleMenu($menu);

    /**
     * @return mixed
     * 设置角色权限
     */
    abstract public function setPermission();

    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * @param $menu
     * @return bool
     * @throws \Exception
     * 删除非本角色权限内的菜单
     */
    public function deleteMenu($menu)
    {
        if ($this->deleteSuperAdmin($menu) || $this->deleteMch($menu)) {
            return true;
        }
        if ($this->deleteRoleMenu($menu)) {
            return true;
        }
        return false;
    }

    /**
     * @param $menu
     * @return bool
     * 去除只允许超级管理员访问的KEY
     */
    public function deleteSuperAdmin($menu)
    {
        if ($this->getName() == 'super_admin') {
            return false;
        }
        if (isset($menu['key']) && in_array($menu['key'], Menus::MALL_SUPER_ADMIN_KEY)) {
            return true;
        }
        return false;
    }

    /**
     * @param $menu
     * @return bool
     * 去除只允许多商户访问的KEY
     */
    public function deleteMch($menu)
    {
        if ($this->getName() == 'mch') {
            return false;
        }
        if (isset($menu['route']) && in_array($menu['route'], Menus::MALL_MCH_KEY)) {
            return true;
        }
        return false;
    }

    /**
     * @return array
     * 获取分销订单的菜单
     */
    public function getDistributionMenu()
    {
        $menuList = [
            [
                'sign' => 'mall',
                'name' => '商城'
            ]
        ];
        $list = \Yii::$app->plugin->getList();
        foreach ($list as $item) {
            try {
                $plugin = \Yii::$app->plugin->getPlugin($item->name);
                if (!$this->checkPlugin($plugin)) {
                    continue;
                }
                $orderConfig = $plugin->getOrderConfig();
                //2019年11月22日 09:05:26    增加礼物说菜单
                if (($orderConfig->support_share == 1 || $item->name == 'gift') && $plugin->getName() != 'mch') {
                    $menuList[] = [
                        'sign' => $plugin->getName(),
                        'name' => $plugin->getDisplayName()
                    ];
                }
            } catch (\Exception $exception) {
                continue;
            }
        }
        return $menuList;
    }

    public $showDetail = false;

    /**
     * @return array
     * 获取已安装插件
     */
    public function getInstalledPluginList()
    {
        $list = $this->getPluginList();
        $plugins = [];
        foreach ($list as $plugin) {
            $plugins[] = [
                'name' => $plugin->getName(),
                'display_name' => $plugin->getDisplayName(),
                'pic_url' => $plugin->getIconUrl(),
                'route' => $this->getPluginIndexRoute($plugin),
                'showDetail' => $this->showDetail
            ];
        };
        return $plugins;
    }

    /**
     * @param Plugin $plugin
     * @return mixed
     */
    protected function getPluginIndexRoute($plugin)
    {
        return $plugin->getIndexRoute();
    }

    /**
     * @param Plugin $plugin
     * @return bool
     * 检测插件是否有效
     */
    public function checkPlugin($plugin)
    {
        return false;
    }

    /**
     * @return array
     * 获取未安装插件
     */
    public function getNotPluginList()
    {
        return [];
    }

    /**
     * @return Plugin[]
     * 获取插件列表
     */
    public function getPluginList()
    {
        if ($this->pluginList && is_array($this->pluginList)) {
            return $this->pluginList;
        }
        $list = \Yii::$app->plugin->getList();
        $plugins = [];
        foreach ($list as $Plugin) {
            try {
                $plugin = \Yii::$app->plugin->getPlugin($Plugin->name);
                if (!$this->checkPlugin($plugin)) {
                    continue;
                }
                $plugins[$Plugin->name] = $plugin;
            } catch (ClassNotFoundException $exception) {
                continue;
            }
        };
        $this->pluginList = $plugins;
        return $plugins;
    }

    /**
     * @param $name
     * @return Plugin
     * @throws \Exception
     */
    public function getPlugin($name)
    {
        if (!$this->pluginList) {
            $this->pluginList = $this->getPluginList();
        }
        if (empty($this->pluginList[$name])) {
            throw new \Exception('不存在权限');
        }
        return $this->pluginList[$name];
    }

    /**
     * @return array
     * 账户权限
     */
    public function getAccountPermission()
    {
        return $this->permission;
    }

    /**
     * @param $item
     * @return bool
     * 校验链接是否显示
     */
    public function checkLink($item)
    {
        if (!isset($item['key'])) {
            return true;
        }
        $permission = $this->getAccountPermission();
        if (in_array($item['key'], $permission)) {
            return true;
        }
        return false;
    }

    /**
     * 获取当前商城所属账号角色
     * @return AdminRole|SuperAdminRole
     * @throws \Exception
     */
    public function getMallRole()
    {
        /* @var Admin $admin */
        $admin = $this->mall->admin;
        $config = [
            'admin' => $admin,
            'mall' => $this->mall
        ];
        if ($admin->admin_type == Admin::ADMIN_TYPE_SUPER) {
            $parent = new SuperAdminRole($config);
        } elseif ($admin->admin_type == Admin::ADMIN_TYPE_ADMIN) {
            $parent = new AdminRole($config);
        } else {
            throw new \Exception('错误的账户');
        }
        return $parent;
    }

    /**
     * @return $this
     * @throws \Exception
     * 获取商城所属的子账号或总账号权限
     */
    public function getAccount()
    {
        return $this;
    }

    /**
     * @return array
     * 获取二级权限：上传设置权限、模板市场权限
     */
    public function getSecondaryPermission()
    {
        return AuthLogic::getSecondaryPermission();
    }

    /**
     * @return array
     * @throws \Exception
     * 获取模板中心的使用权限
     */
    public function getTemplate()
    {
        $secondaryPermission = $this->getSecondaryPermission();
        return $secondaryPermission['template'];
    }
}
