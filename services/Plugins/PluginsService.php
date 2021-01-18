<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 检查插件
 * Author: xuyaoxiang
 * Date: 2020/9/28
 * Time: 17:56
 */

namespace app\services\Plugins;

use app\models\Plugin;

class PluginsService
{
    //检查插件是否安装和插件文件是否存在;
    /**
     * @param $plugin_name //插件名称
     * @return bool
     */
    static public function isInstalled($plugin_name)
    {
        $model = Plugin::find()->where(['name' => $plugin_name, 'is_delete' => 0])->one();

        if (!$model) {

            return false;
        }

        $class_name = "app\\plugins\\" . $plugin_name . "\\Plugin";

        if (!class_exists($class_name)) {

            return false;
        }

        return true;
    }
}