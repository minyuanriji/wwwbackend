<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 后台菜单
 * Author: zal
 * Date: 2020-04-08
 * Time: 16:12
 */

namespace app\controllers\admin;

use app\core\ApiCode;
use app\forms\admin\permission\menu\MenusForm;

class MenusController extends AdminController
{
    public function actionIndex()
    {
        $route = \Yii::$app->request->post('route');
        $form = new MenusForm();
        $form->currentRoute = $route;
        $res = $form->getMenus('admin');
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
