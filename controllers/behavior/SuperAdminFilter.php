<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 超级管理员过滤器
 * Author: zal
 * Date: 2020-04-09
 * Time: 17:18
 */

namespace app\controllers\behavior;

use app\core\ApiCode;
use app\models\Admin;
use yii\base\ActionFilter;

class SuperAdminFilter extends ActionFilter
{
    public $loginUrl;
    public $safeActions;
    public $onlyActions;
    public $safeRoutes;

    public function beforeAction($action)
    {
        if (\Yii::$app->admin->isGuest) {
            return false;
        }
        if (is_array($this->safeActions) && in_array($action->id, $this->safeActions)) {
            return parent::beforeAction($action);
        }
        if (is_array($this->safeRoutes) && in_array(\Yii::$app->requestedRoute, $this->safeRoutes)) {
            return parent::beforeAction($action);
        }
        if (is_array($this->onlyActions) && !in_array($action->id, $this->onlyActions)) {
            return parent::beforeAction($action);
        }
        /** @var Admin $admin */
        $admin = \Yii::$app->admin->identity;
        if ($admin->admin_type == Admin::ADMIN_TYPE_SUPER) {
            return parent::beforeAction($action);
        }
        if (\Yii::$app->request->isAjax) {
            \Yii::$app->response->data = [
                'code' => ApiCode::CODE_NOT_LOGIN,
                'msg' => '您不是超级管理员，没有访问权限!',
            ];
        } else {
            if (!$this->loginUrl) {
                $this->loginUrl = \Yii::$app->urlManager->createAbsoluteUrl(['admin/admin/login']);
            }
            \Yii::$app->response->redirect($this->loginUrl);
        }
        return false;
    }
}
