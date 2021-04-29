<?php
namespace app\mch\controllers;

use app\core\ApiCode;
use app\mch\forms\permission\menu\MenusForm;

class MenusController extends MchController {

    public function actionIndex(){

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
}