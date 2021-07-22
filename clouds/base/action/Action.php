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
use app\clouds\base\tables\CloudActionRoute;
use app\clouds\base\tables\CloudProject;
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
     * 获取授权组件
     * @return Action|null
     */
    public function getAuthAction()
    {
        $actionModel = Table::findOne(CloudAction::class, $this->actionModel->auth_action_id);
        if($actionModel && !$actionModel->is_deleted)
        {
            return static::find($actionModel);
        }
        return null;
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
     * 通过路由获取操作对象
     * @param Route $route
     * @return Action
     * @throws CloudException
     */
    public static function findByRoute(Route $route)
    {
        $actionRoute = Table::find(CloudActionRoute::class)->where([
            "host_name" => $route->host,
            "path_uri"  => $route->pathURI
        ])->one();
        if(!$actionRoute)
        {
            throw new CloudException("无法访问 " . $route->scheme . "://" . $route->host . $route->pathURI);
        }

        $actionModel = Table::findOne(CloudAction::class, $actionRoute->action_id);
        if(!$actionModel || $actionModel->is_deleted)
        {
            throw new CloudException("CloudAction:".$actionRoute->action_id."对象不存在");
        }
        return static::find($actionModel);
    }

    /**
     * 获取操作对象
     * @param CloudAction $actionModel
     * @return CloudAction|null
     * @throws CloudException
     */
    public static function find(CloudAction $actionModel)
    {
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
        $projectClass = "{$classDir}\\Project";
        if(!class_exists($projectClass))
        {
            throw new CloudException("”".$projectClass."“项目类不存在");
        }

        $class = new $projectClass($projectModel);

        return $class;
    }

    /**
     * 获取所在命名空间
     * @return string
     */
    public function getNamespace()
    {
        $actionDir = FuncHelper::convertNamesPath($this->actionModel->class_dir);
        return $this->module->getNamespace() . "\\{$actionDir}";
    }

    /**
     * 返回云操作数据对象模型
     * @return CloudAction
     */
    public function getModel()
    {
        return $this->actionModel;
    }

    /**
     * 返回目录路径
     * @return string
     */
    public function getDirPath()
    {
        $reflector = new \ReflectionClass(get_class($this));
        return dirname($reflector->getFileName());
    }
}