<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-06 15:49
 */
namespace app\clouds\action;

use app\clouds\errors\NotFound404;
use app\clouds\route\Route;
use app\clouds\tables\CloudAction;
use app\clouds\tables\CloudModule;
use app\clouds\tables\CloudProject;
use app\clouds\tables\CloudUserApp;
use app\clouds\tables\CloudUserAppRouteList;
use app\clouds\tables\Table;
use yii\base\BaseObject;

class Action extends BaseObject
{
    private $action;
    private $user_app;
    private $route;
    private $route_list_item;
    private $project = null;
    private $module = null;

    public function __construct(CloudAction $action, CloudUserAppRouteList $routeItem, CloudUserApp $userApp, Route $route, $config = [])
    {
        parent::__construct($config);

        $this->action          = $action;
        $this->user_app        = $userApp;
        $this->route           = $route;
        $this->route_list_item = $routeItem;
    }

    /**
     * 获取操作对象
     * @param CloudUserApp $userApp
     * @param Route $route
     * @return Action
     * @throws NotFound404
     */
    public static function find(CloudUserApp $userApp, Route $route)
    {
        $routeItem = Table::find(CloudUserAppRouteList::class)->where([
            "user_id"  => $userApp->user_id,
            "app_id"   => $userApp->id,
            "path_uri" => $route->pathURI
        ])->one();
        if(!$routeItem)
        {
            throw new NotFound404("”".$userApp->pathURI."“路由对象不存在");
        }

        $action = Table::findOne(CloudAction::class, $routeItem->action_id);
        if(!$action)
        {
            throw new NotFound404("”".$routeItem->action_id."“操作对象不存在");
        }

        return new Action($action, $routeItem, $userApp, $route);
    }

    public function getNamespaceDir()
    {
        if(!$this->project)
        {
            $this->project = Table::findOne(CloudProject::class, $this->action->project_id);
        }

        if(!$this->project || $this->project->is_deleted)
        {
            throw new NotFound404("”".$this->action->project_id."“项目不存在");
        }

        if(!$this->module)
        {
            $this->module = Table::findOne(CloudModule::class, $this->action->module_id);
        }

        if(!$this->module || $this->module->is_deleted)
        {
            throw new NotFound404("”".$this->action->module_id."“模块不存在");
        }

        $classDirs[] = str_replace("/", "\\", $this->project->class_dir);
        $classDirs[] = str_replace("/", "\\", $this->module->class_dir);
        if(!empty($this->action->class_dir))
        {
            $classDirs[] = str_replace("/", "\\", $this->action->class_dir);
        }

        return implode("\\", $classDirs);
    }

    /**
     * 获取操作对象
     * @return CloudAction
     */
    public function getCloudAction()
    {
        return $this->action;
    }
}