<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-06 15:49
 */
namespace app\clouds\action;

use app\clouds\errors\CloudException;
use app\clouds\helpers\UserHelper;
use app\clouds\route\Route;
use app\clouds\tables\CloudAction;
use app\clouds\tables\CloudModule;
use app\clouds\tables\CloudProject;
use app\clouds\tables\CloudUserApp;
use app\clouds\tables\CloudUserAppRouteList;
use app\clouds\tables\Table;
use app\clouds\user\User;
use yii\base\BaseObject;

abstract class Action extends BaseObject
{
    private $actionModel;
    private $userAppModel;
    private $project = null;
    private $module = null;

    public function __construct(CloudAction $actionModel,  CloudUserApp $userAppModel,  $config = [])
    {
        parent::__construct($config);

        $this->actionModel  = $actionModel;
        $this->userAppModel = $userAppModel;
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
        if(!$actionModel)
        {
            throw new CloudException("”".$routeItem->action_id."“操作对象不存在");
        }

        //模块
        $moduleModel =
        $actionClass = "";

        //return new Action($action, $routeItem, $userApp, $route);
    }

    /**
     * 权限判断
     * @return boolean
     */
    public function allowAccess()
    {
        $allowAccess = $this->action->security == "private" ? false : true;

        //操作需要授权访问
        if($this->action->security == "authorize")
        {
            $identity = UserHelper::getIdentity();

        }

        return $allowAccess;
    }

    /**
     * 返回操作命名空间
     * @return string
     * @throws CloudException
     */
    public function getNamespaceDir()
    {
        if(!$this->project)
        {
            $this->project = Table::findOne(CloudProject::class, $this->action->project_id);
        }

        if(!$this->project || $this->project->is_deleted)
        {
            throw new CloudException("”".$this->action->project_id."“项目不存在");
        }

        if(!$this->module)
        {
            $this->module = Table::findOne(CloudModule::class, $this->action->module_id);
        }

        if(!$this->module || $this->module->is_deleted)
        {
            throw new CloudException("”".$this->action->module_id."“模块不存在");
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