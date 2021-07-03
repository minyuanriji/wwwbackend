<?php
/**
 * 登录过滤器
 */

namespace app\mch\behavior;

use app\core\ApiCode;
use yii\base\ActionFilter;
use Yii;

class LoginFilter extends ActionFilter{
    public $loginUrl;
    public $safeActions;
    public $onlyActions;
    public $safeRoutes;

    public function beforeAction($action){

        if (is_array($this->safeActions) && in_array($action->id, $this->safeActions)) {
            return parent::beforeAction($action);
        }
        if (is_array($this->safeRoutes) && in_array(\Yii::$app->requestedRoute, $this->safeRoutes)) {
            return parent::beforeAction($action);
        }
        if (is_array($this->onlyActions) && !in_array($action->id, $this->onlyActions)) {
            return parent::beforeAction($action);
        }

        if (!\Yii::$app->mchAdmin->isGuest) {
            return parent::beforeAction($action);
        }
        if (\Yii::$app->request->isAjax) {
            \Yii::$app->response->data = [
                'code' => ApiCode::CODE_NOT_LOGIN,
                'msg' => '请先登录。',
            ];
        } else {
            if (!$this->loginUrl) {
                // cookie存储最后一个登录角色相关信息
                $url = isset($_COOKIE['__mch_login_route']) ? $_COOKIE['__mch_login_route'] : 'mch/admin/login';
                $data = [$url];
                $this->loginUrl = \Yii::$app->urlManager->createAbsoluteUrl($data);
            }
            \Yii::$app->response->redirect($this->loginUrl);
        }
        return false;
    }
}
