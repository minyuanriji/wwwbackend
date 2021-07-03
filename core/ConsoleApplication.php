<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 控制台核心应用程序入口
 * Author: zal
 * Date: 2020-05-05
 * Time: 14:56
 */

namespace app\core;

use yii\helpers\ArrayHelper;

/***
 * Class Application
 * @package app\core
 */
class ConsoleApplication extends \yii\console\Application
{
    use Application;

    public function __construct($config = null)
    {
        $this->checkEnv()
            ->loadDotEnv()
            ->defineConstants();

        require_once __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

        if (!$config) {
            $config = ArrayHelper::merge(
                require __DIR__ . '/../config/console.php',
                file_exists(__DIR__ . '/../config/console-local.php') ? require __DIR__ . '/../config/console-local.php' : []
            );
        }

        parent::__construct($config);

        $this->responseAsJson()
            ->loadErrorReporting();

        $this->loadAppLogger()
            ->registerHandlers()
            ->loadAppPlugins();
    }

    /**
     * 检查服务器php环境
     * @return $this
     * @throws \Exception
     */
    protected function checkEnv()
    {
        $checkFunctions = [
            'proc_open',
            'proc_get_status',
        ];
        if (version_compare(PHP_VERSION, '7.2.0') < 0) {
            throw new \Exception('PHP版本不能小于7.2，当前PHP版本为' . PHP_VERSION);
        }
        foreach ($checkFunctions as $function) {
            if (!function_exists($function)) {
                throw new \Exception('PHP函数' . $function . '已被禁用，请先取消禁用' . $function . '函数');
            }
        }
        return $this;
    }

    public function getSession()
    {
        return \Yii::$app->session;
    }
}
