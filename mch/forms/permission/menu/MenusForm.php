<?php
namespace app\mch\forms\permission\menu;

use app\mch\forms\Menus;
use app\models\BaseModel;

class MenusForm extends BaseModel {

    public $currentRouteInfo = [];
    public $currentRoute;
    public $type;
    public $isExist = false;

    /**
     * 有实际页面且不菜单列表中的路由填写在此处
     */
    const existList = [

    ];

    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * @note 通过路由来判断是否启用插件菜单
     * @param string $route
     * @return array
     */
    public function getMenusByRoute($route = null)
    {

        $menus = Menus::getMenus();

        $menus = $this->resetMenus($menus);

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
     * 给自定义路由列表 追加ID 及 PID
     * @param array $list 自定义的多维路由数组
     * @param int $id 权限ID
     * @param int $pid 权限PID
     * @return mixed
     */
    private function resetMenus(array $list, &$id = 1, $pid = 0){
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