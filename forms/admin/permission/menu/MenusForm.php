<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-10
 * Time: 09:55
 */

namespace app\forms\admin\permission\menu;

use app\forms\admin\Menus;
use app\forms\admin\permission\role\BaseRole;
use app\forms\admin\permission\branch\BaseBranch;

use app\models\BaseModel;
use app\plugins\Plugin;

/**
 * @property BaseBranch $branch
 * @property BaseRole $role
 */
class MenusForm extends BaseModel
{
    private $branch;
    private $role;

    public $currentRouteInfo = [];
    public $currentRoute;
    public $type;
    public $isExist = false;

    /**
     * 有实际页面且不菜单列表中的路由填写在此处
     */
    const existList = [
        'mall/overview/index',
        'admin/cache/clean',
        'admin/index/index',
        'admin/index'
    ];

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->branch = \Yii::$app->branch;
        $this->role = \Yii::$app->role;
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-30
     * @Time: 17:08
     * @Note:通过路由来判断是否启用插件菜单
     * @param string $route
     */
    public function getMenusByRoute($route = null)
    {
        $route = ltrim($route, '/');
        $pattern = '/^plugin\/.*/';
        preg_match($pattern, $route, $matches);
        /**
         * @var Plugin $plugin
         */
        $originalMenus = Menus::getMallMenus();
        if ($matches) {
            $originRoute = $matches[0];
            $originRouteArray = mb_split('/', $originRoute);
            $pluginId = !empty($originRouteArray[1]) ? $originRouteArray[1] : null;
            $pluginClass = \Yii::$app->plugin->getPlugin($pluginId);
            $plugin = new $pluginClass();
            foreach ($originalMenus as $i => $menu) {
                if ($menu['key'] == 'plugin-center') {
                    $originalMenus[$i]['children'] = array_merge([
                        [
                            'name' => '插件中心',
                            'route' => 'mall/plugin/index',
                            'icon' => 'el-icon-setting'
                        ]
                    ], $plugin->getMenus());
                }
            }
        } else {
            //商城路由
            $originalMenus = Menus::getMallMenus();
        }
        // 去除不需显示的菜单
        $menus = $this->deleteMenu($originalMenus);
        // 菜单列表
        $menus = $this->resetMenus($menus);


        if (!$this->isExist) {
            if (!in_array($this->currentRoute, self::existList)) {
                // 开启调试模式才显示
                if (env('YII_DEBUG')) {
                    throw new \Exception('页面路由未正常配置（会导致员工账号无法进入该页面）,请检查');
                }
            }
        }
        $courseMenu = [];
        foreach ($menus as $key => $menu) {
            // 教程管理菜单 移到顶部显示
            if (isset($menu['key']) && $menu['key'] == 'course') {
                $courseMenu = $menu;
                unset($menus[$key]);
                break;
            }
        }
        $menus = array_values($menus);
        return [
            'menus' => $menus,
            'currentRouteInfo' => $this->currentRouteInfo,
            'courseMenu' => $courseMenu,
        ];
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-04
     * @Time: 15:15
     * @Note:获取菜单栏
     * @param $type
     * @return array
     * @throws \Exception
     */
    public function getMenus($type)
    {
        if (!in_array($type, ['admin', 'mall', 'plugin'])) {
            throw new \Exception('type 传入参数无效');
        }
        switch ($type) {
            case 'admin':
                $originalMenus = Menus::getAdminMenus();
                break;
            case 'mall':
                $originalMenus = Menus::getMallMenus();
                break;
            case 'plugin':
                $plugin = \Yii::$app->plugin->currentPlugin;
                $originalMenus = $plugin->getMenus();
                break;
            default:
                throw new \Exception('type 传入参数无效');
        }


        // 去除不需显示的菜单
        $menus = $this->deleteMenu($originalMenus);
        // 菜单列表
        $menus = $this->resetMenus($menus);

        if (!$this->isExist) {
            if (!in_array($this->currentRoute, self::existList)) {
                // 开启调试模式才显示
                if (env('YII_DEBUG')) {
                    throw new \Exception('页面路由未正常配置（会导致员工账号无法进入该页面）,请检查');
                }
            }
        }
        $courseMenu = [];
        foreach ($menus as $key => $menu) {
            // 教程管理菜单 移到顶部显示
            if (isset($menu['key']) && $menu['key'] == 'course') {
                $courseMenu = $menu;
                unset($menus[$key]);
                break;
            }
        }
        $menus = array_values($menus);

        return [
            'menus' => $menus,
            'currentRouteInfo' => $this->currentRouteInfo,
            'courseMenu' => $courseMenu,
        ];
    }

    /**
     * 去除非本分支和本角色拥有的菜单
     * @Author: zal
     * @Date: 2020-04-10
     * @Time: 09:50
     * @param $menus
     * @return array
     * @throws \Exception
     */
    public function deleteMenu($menus)
    {
        foreach ($menus as $index => $item) {
            $menus[$index]['is_show'] = true;
            if ($this->branch->deleteMenu($item)) {
                unset($menus[$index]);
                continue;
            }
            if (isset($item['children']) && is_array($item['children'])) {
                $item['children'] = $this->deleteMenu($item['children']);
                if (count($item['children']) <= 0) {
                    unset($menus[$index]);
                    continue;
                } else {
                    $item['route'] = $item['children'][0]['route'];
                    $menus[$index]['route'] = $item['children'][0]['route'];
                    $menus[$index]['children'] = $item['children'];
                }
            }
            if ($this->role->deleteMenu($item)) {
                unset($menus[$index]);
                continue;
            }
        }
        $menus = array_values($menus);
        return $menus;
    }

    /**
     * 给自定义路由列表 追加ID 及 PID
     * @Author: zal
     * @Date: 2020-04-10
     * @Time: 09:50
     * @param array $list 自定义的多维路由数组
     * @param int $id 权限ID
     * @param int $pid 权限PID
     * @return mixed
     */
    private function resetMenus(array $list, &$id = 1, $pid = 0)
    {



        foreach ($list as $key => $item) {
            $list[$key]['id'] = (string)$id;
            $list[$key]['pid'] = (string)$pid;
            // 前端选中的菜单

            if (isset($list[$key]['route']) && $this->currentRoute === $list[$key]['route']) {
                $this->currentRouteInfo = $list[$key];
                $list[$key]['is_active'] = true;
                $this->isExist = true;
            }
            if (isset($list[$key]['action'])) {
                foreach ($list[$key]['action'] as $aItem) {
                    if (isset($aItem['route']) && $aItem['route'] === $this->currentRoute) {
                        $list[$key]['is_active'] = true;
                        $this->isExist = true;
                    }
                }
            }

            if (isset($item['children'])) {
                $id++;
                $list[$key]['children'] = $this->resetMenus($item['children'], $id, $id - 1);
                foreach ($list[$key]['children'] as $cKey => $child) {
                    if (isset($child['is_active']) && $child['is_active'] == true) {
                        $list[$key]['is_active'] = true;
                    }
                }
            }
            if (isset($item['action'])) {
                $id++;
                $list[$key]['action'] = $this->resetMenus($item['action'], $id, $id - 1);
            }
            isset($item['children']) == false && isset($item['action']) == false ? $id++ : $id;
        }

        return $list;
    }
}
