<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-05 16:03
 */
namespace app\clouds;

use app\clouds\auth\AccessAuth;
use app\clouds\base\action\Action;
use app\clouds\base\consts\Code;
use app\clouds\base\errors\CloudException;
use app\clouds\base\helpers\IdentityHelper;
use app\clouds\base\route\Route;
use app\clouds\base\tables\CloudUserApp;
use app\clouds\base\tables\Table;
use yii\base\BaseObject;

class AppEngine extends BaseObject
{
    private $route;

    public static function run(CloudApplication $cloudApp, Route $route)
    {
        $userApp = Table::find(CloudUserApp::class)->where([
            "host"       => $route->host,
            "is_deleted" => 0
        ])->one();
        if(!$userApp)
        {
            throw new CloudException("无法获取”".$route->host."“应用信息");
        }

        //获取用户操作对象
        $action = Action::find($userApp, $route);

        //访问权限判断
        $auth = new AccessAuth($action);
        if(!$auth->pass())
        {
            throw new CloudException("无权限访问:" . $action->getModel()->class_dir, Code::ACTION_NOT_ALLOW_ACCESS);
        }

        //设置操作命名空间
        $cloudApp->controllerNamespace = $action->getNamespaceDir();

        //强制指向到自定义路由
        $cloudApp->catchAll[0] = $action->getCloudAction()->controllerID . "/" . $action->getCloudAction()->actionID;
    }

}