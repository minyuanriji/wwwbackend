<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * 路由
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-05 14:31
 */
namespace app\clouds\route;

use app\clouds\errors\RequestException;
use yii\base\BaseObject;

class Route extends BaseObject
{
    public $scheme;
    public $host;
    public $pathURI;

    /**
     * 路由解析
     * @return Route
     * @throws RequestException
     * @throws \yii\base\InvalidConfigException
     */
    public static function parse()
    {
        $route = new Route();

        $request = \Yii::$app->getRequest();
        $parseArray = parse_url($request->getHostInfo());
        if (empty($parseArray['host']))
        {
            throw new RequestException("访问域名不能为空");
        }

        $route->host    = trim(strtolower($parseArray['host']));
        $route->scheme  = !empty($parseArray['scheme']) && strtolower($parseArray['scheme']) == "https" ? "https" : "http";
        $route->pathURI = "/" . trim(ltrim(rtrim($request->getPathInfo(),"/"), "/"));

        return $route;
    }
}