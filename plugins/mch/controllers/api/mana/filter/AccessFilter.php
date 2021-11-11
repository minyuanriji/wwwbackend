<?php
namespace app\plugins\mch\controllers\api\mana\filter;

use app\core\ApiCode;
use app\plugins\mch\controllers\api\mana\MchAdminController;
use yii\base\ActionFilter;

class AccessFilter extends ActionFilter{

    public $denyRoutes;
    public $only;

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action){
        $route = \Yii::$app->requestedRoute;
        if (is_array($this->denyRoutes) && in_array($route, $this->denyRoutes)) {
            if(MchAdminController::$adminUser['is_sub']){
                \Yii::$app->response->data = [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '子账号无权限操作 -1',
                ];
                return false;
            }
        }
        return true;
    }
}