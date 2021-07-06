<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * 云应用
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-06-26 16:26
 */

namespace app\clouds;


use app\clouds\apps\AppEngine;
use app\clouds\errors\BaseException;
use app\clouds\errors\NotFound404;
use app\clouds\errors\RequestException;
use app\clouds\route\Route;
use yii\base\UnknownClassException;

class CloudApplication extends \yii\web\Application
{
    public function run()
    {
        try {
            AppEngine::run($this, Route::parse());
        }catch (BaseException $e){
            echo $e->getMessage() . "\n";
            echo $e->getFile() . "\n";
            echo $e->getLine();
            exit;
        }
        exit;

        $this->controllerNamespace = "app\\clouds\\apps";

        $className = "app\\clouds\\PROJECTS\\smart_house\\Project";
        $projectObject = new $className();
        if(!$projectObject instanceof IProject){
            throw new UnknownClassException("项目类[{$className}]未实现[IProject]接口");
        }

        $this->name = $projectObject->getName();
        $this->version = $projectObject->getVersion();
        $this->controllerNamespace = dirname(get_class($projectObject)) . "\\controllers";

        $projectObject->init();

        return parent::run(); // TODO: Change the autogenerated stub
    }
}