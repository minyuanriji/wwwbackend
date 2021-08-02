<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 菜单
 * Author: zal
 * Date: 2020-04-10
 * Time: 09:49
 */

namespace app\controllers\mall;

use app\core\ApiCode;
use app\forms\admin\permission\menu\MenusForm;

class MenusController extends MallController
{
    public function actionIndex()
    {
        $route = \Yii::$app->request->post('route');

        $form = new MenusForm();
        $form->currentRoute = $route;
        $res = $form->getMenusByRoute($route);
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'menus' => $res['menus'],
                'currentRouteInfo' => $res['currentRouteInfo'],
                'courseMenu' => $res['courseMenu']
            ]
        ]);
    }

    public function actionPlugin($name)
    {
        \Yii::$app->plugin->setCurrentPlugin(\Yii::$app->plugin->getPlugin($name));
        $route = \Yii::$app->request->post('route');
        $form = new MenusForm();
        $form->currentRoute = $route;
        $res = $form->getMenus('plugin');
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'menus' => $res['menus'],
                'currentRouteInfo' => $res['currentRouteInfo']
            ]
        ]);
    }
}
