<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-05 16:03
 */
namespace app\clouds;

use app\clouds\base\action\Action;
use app\clouds\base\auth\AccessAuth;
use app\clouds\base\consts\Code;
use app\clouds\base\errors\CloudException;
use app\clouds\base\helpers\IdentityHelper;
use app\clouds\base\route\Route;
use yii\base\BaseObject;
use Yii;

class AppEngine extends BaseObject
{
    public static function run(CloudApplication $cloudApp, Route $route)
    {
        define("CLOUD_HTTP_STATIC_PATH", Yii::$app->request->baseUrl . "/clouds/statics");

        //获取用户操作对象
        $action = Action::findByRoute($route);

        //访问权限判断
        $auth = new AccessAuth($action);
        if(!$auth->pass())
        {
            $authAction = null;
            if(!IdentityHelper::isLogin() && !Yii::$app->getRequest()->isAjax)
            {
                $authAction = $action->getAuthAction();
            }
            if(!$authAction || !($authAction instanceof Action))
            {
                throw new CloudException("无权限访问:" . $action->getModel()->class_dir, Code::ACTION_NOT_ALLOW_ACCESS);
            }
            $action = $authAction;
        }

        //设置操作命名空间
        $cloudApp->controllerNamespace = $action->getNamespace() . "\\controllers";

        $cloudApp->setViewPath($action->getDirPath() . "/views");

        //强制指向到自定义路由
        $cloudApp->catchAll[0] = $action->getModel()->controllerID . "/" . $action->getModel()->actionID;
    }

}