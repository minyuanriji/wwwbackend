<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-10 14:35
 */
namespace app\clouds\base\project;

use app\clouds\base\errors\CloudException;
use app\clouds\base\helpers\FuncHelper;
use app\clouds\base\module\Module;
use app\clouds\base\tables\CloudModule;
use app\clouds\base\tables\CloudProject;
use app\clouds\base\tables\Table;
use yii\base\BaseObject;

abstract class Project extends BaseObject
{

    private $projectModel;

    private $modules = [];

    public function __construct(CloudProject $projectModel, $config = [])
    {
        parent::__construct($config);

        $this->projectModel = $projectModel;
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
     * 获取所在命名空间
     * @return string
     */
    public function getNamespace()
    {
        $projectDir = FuncHelper::convertNamesPath($this->projectModel->class_dir);
        return "app\\clouds\\apps\\{$projectDir}";;
    }

    /**
     * 获取模块对象
     * @param $module_id
     * @return Module
     * @throws CloudException
     */
    public function getModule($module_id)
    {
        if(!isset($this->modules[$module_id]))
        {
            $moduleModel = Table::findOne(CloudModule::class, $module_id);
            if(!$moduleModel || $moduleModel->is_deleted)
            {
                throw new CloudException("”".$module_id."“模块不存在");
            }

            $moduleDir = FuncHelper::convertNamesPath($moduleModel->class_dir);
            $moduleClass = $this->getNamespace() . "\\{$moduleDir}\\Module";
            if(!class_exists($moduleClass))
            {
                throw new CloudException("”".$moduleClass."“模块类不存在");
            }

            $this->modules[$module_id] = new $moduleClass($this, $moduleModel);
        }

        return isset($this->modules[$module_id]) ? $this->modules[$module_id] : null;
    }

    /**
     * 获取数据模型
     * @return CloudProject
     */
    public function getModel()
    {
        return $this->projectModel;
    }
}