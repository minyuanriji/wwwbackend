<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-06 15:49
 */
namespace app\clouds\base\action;

use app\clouds\base\errors\CloudException;
use app\clouds\base\helpers\FuncHelper;
use app\clouds\base\module\Module;
use app\clouds\base\project\Project;
use app\clouds\base\route\Route;
use app\clouds\base\tables\CloudAction;
use app\clouds\base\tables\CloudProject;
use app\clouds\base\tables\CloudUserApp;
use app\clouds\base\tables\CloudUserAppRouteList;
use app\clouds\base\tables\Table;
use app\clouds\base\user\User;
use yii\base\BaseObject;

abstract class Action extends BaseObject
{
    private $actionModel;
    private $project;
    private $module;

    public function __construct(Project $project, Module $module, CloudAction $actionModel,   $config = [])
    {
        parent::__construct($config);

        $this->actionModel = $actionModel;
        $this->project = $project;
        $this->module = $module;
    }

    /**
     * 获取项目对象
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * 获取模块对象
     * @return Module
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * 获取操作对象
     * @param CloudUserApp $userApp
     * @param Route $route
     * @return Action
     * @throws CloudException
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
            throw new CloudException("”".$userApp->pathURI."“路由对象不存在");
        }

        $actionModel = Table::findOne(CloudAction::class, $routeItem->action_id);
        if(!$actionModel || $actionModel->is_deleted)
        {
            throw new CloudException("”".$routeItem->action_id."“功能不存在");
        }

        $project = static::findProject($actionModel->project_id);

        $module = $project->getModule($actionModel->module_id);

        return $module->getAction($actionModel->id);
    }

    /**
     * 获取项目对象
     * @param CloudAction $actionModel
     * @return Project
     * @throws CloudException
     */
    protected static function findProject($project_id)
    {
        $projectModel = Table::findOne(CloudProject::class, $project_id);
        if(!$projectModel || $projectModel->is_deleted)
        {
            throw new CloudException("”".$project_id."“项目不存在");
        }

        $classDir = FuncHelper::convertNamesPath($projectModel->class_dir);
        $projectClass = "app\\clouds\\apps\\{$classDir}\\Project";
        if(!class_exists($projectClass))
        {
            throw new CloudException("”".$projectClass."“项目类不存在");
        }

        $class = new $projectClass($projectModel);

        return $class;
    }

    /**
     * 返回操作命名空间
     * @return string
     * @throws CloudException
     */
    public function getNamespaceDir()
    {
        $dirs[] = FuncHelper::convertNamesPath($this->project->getModel()->class_dir);
        $dirs[] = FuncHelper::convertNamesPath($this->module->getModel()->class_dir);
        $dirs[] = FuncHelper::convertNamesPath($this->actionModel->class_dir);
        return "app\\clouds\\apps\\" . implode("\\", $dirs);
    }

    /**
     * 返回云操作数据对象模型
     * @return CloudAction
     */
    public function getModel()
    {
        return $this->actionModel;
    }
}