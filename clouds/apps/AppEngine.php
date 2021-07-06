<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-05 16:03
 */
namespace app\clouds\apps;

use app\clouds\CloudApplication;
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
            throw new NotFound404("无法获取”".$route->host."“应用信息");
        }
    }

}