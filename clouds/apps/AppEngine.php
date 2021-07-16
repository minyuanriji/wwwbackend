<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-05 16:03
 */
namespace app\clouds\apps;

use app\clouds\action\Action;
use app\clouds\CloudApplication;
use app\clouds\consts\Code;
use app\clouds\errors\CloudException;
use app\clouds\errors\NotFound404;
use app\clouds\route\Route;
use app\clouds\tables\CloudUserApp;
use app\clouds\tables\Table;
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
        if(!$action->allowAccess())
        {
            throw new CloudException("无权限访问", Code::ACTION_NOT_ALLOW_ACCESS);
        }

        //设置操作命名空间
        $namespaceDir = $action->getNamespaceDir();
        $cloudApp->controllerNamespace = "app\\clouds\\apps\\{$namespaceDir}";

        //强制指向到自定义路由
        $cloudApp->catchAll[0] = $action->getCloudAction()->controllerID . "/" . $action->getCloudAction()->actionID;
    }

}