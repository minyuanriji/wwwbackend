<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-10 11:45
 */
namespace app\clouds\base\module;


use app\clouds\base\errors\CloudException;
use app\clouds\base\helpers\FuncHelper;
use app\clouds\base\project\Project;
use app\clouds\base\tables\CloudAction;
use app\clouds\base\tables\CloudModule;
use app\clouds\base\tables\Table;
use yii\base\BaseObject;

abstract class Module extends BaseObject
{

    private $moduleModel;

    private $actions = [];

    private $project;

    public function __construct(Project $project, CloudModule $moduleModel, $config = [])
    {
        parent::__construct($config);

        $this->moduleModel = $moduleModel;
        $this->project = $project;
    }

    /**
     * 获取所在命名空间
     * @return string
     */
    public function getNamespace()
    {
        $moduleDir = FuncHelper::convertNamesPath($this->moduleModel->class_dir);
        return $this->project->getNamespace() . "\\{$moduleDir}";;
    }

    /**
     * 访问权限判断
     * @return bool
     */
    public function allowAccess()
    {
        return true;
    }

    /**
     * 获取功能对象
     * @param $action_id
     * @return mixed|null
     * @throws CloudException
     */
    public function getAction($action_id)
    {
        if(!isset($this->actions[$action_id]))
        {
            $actionModel = Table::findOne(CloudAction::class, $action_id);
            if(!$actionModel || $actionModel->is_deleted)
            {
                throw new CloudException("”".$action_id."“功能不存在");
            }

            $actionDir  = FuncHelper::convertNamesPath($actionModel->class_dir);
            $actionClass = $this->getNamespace() . "\\{$actionDir}\\Action";
            if(!class_exists($actionClass))
            {
                throw new CloudException("”".$actionClass."“功能类不存在");
            }

            $this->actions[$action_id] = new $actionClass($this->project, $this, $actionModel);
        }

        return isset($this->actions[$action_id]) ? $this->actions[$action_id] : null;
    }

    /**
     * 获取数据模型
     * @return CloudModule
     */
    public function getModel()
    {
        return $this->moduleModel;
    }
}