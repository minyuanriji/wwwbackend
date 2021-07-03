<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 登录过滤器
 * Author: zal
 * Date: 2020-04-08
 * Time: 19:18
 */

namespace app\controllers\behavior;

use app\core\ApiCode;
use yii\base\ActionFilter;

class LoginFilter extends ActionFilter
{
    public $loginUrl;
    public $safeActions;
    public $onlyActions;
    public $safeRoutes;

    public function beforeAction($action)
    {
        if (is_array($this->safeActions) && in_array($action->id, $this->safeActions)) {
            return parent::beforeAction($action);
        }
        if (is_array($this->safeRoutes) && in_array(\Yii::$app->requestedRoute, $this->safeRoutes)) {
            return parent::beforeAction($action);
        }
        if (is_array($this->onlyActions) && !in_array($action->id, $this->onlyActions)) {
            return parent::beforeAction($action);
        }
        if (!\Yii::$app->admin->isGuest) {
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
                $url = isset($_COOKIE['__login_route']) ? $_COOKIE['__login_route'] : 'admin/admin/login';
                $mallId = isset($_COOKIE['__mall_id']) ? $_COOKIE['__mall_id'] : 0;
                $mallId = base64_encode($mallId);
                $role = isset($_COOKIE['__login_role']) ? $_COOKIE['__login_role'] : 'admin';
                if ($role == 'mch') {
                    $data = [$url, 'mall_id' => $mallId];
                } elseif ($role == 'staff') {
                    $data = [$url, 'mall_id' => $mallId];
                } else {
                    $data = [$url];
                }

                $this->loginUrl = \Yii::$app->urlManager->createAbsoluteUrl($data);
            }
            \Yii::$app->response->redirect($this->loginUrl);
        }
        return false;
    }
}
