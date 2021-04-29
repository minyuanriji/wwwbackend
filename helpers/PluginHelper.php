<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 序列化相关帮助类
 * Author: zal
 * Date: 2020-04-09
 * Time: 18:49
 */

namespace app\helpers;

use app\models\BaseModel;
use app\models\Plugin;
use app\models\User;
use Prophecy\Exception\Doubler\ClassNotFoundException;
use yii\base\Component;
use yii\helpers\FileHelper;

/**
 * Class PluginHelp
 * @package app\core
 * @property array $list
 * @property \app\helpers\PluginHelper $currentPlugin
 */
class PluginHelper extends Component
{
    private $xPlugins;
    private $xCurrentPlugin;
    private $list;

    /**
     * 获取数据库标记的已安装的插件列表
     * @return array|Plugin[]
     */
    public function getList()
    {
        if ($this->list) {
            return $this->list;
        }
        $this->list = Plugin::find()->where([
            'is_delete' => 0,
        ])->all();
        foreach ($this->list as $item) {
            $Class = '\\app\\plugins\\' . $item->name . '\\Plugin';
            set_error_handler(function () {
            });
            if (class_exists($Class)) {
                /** @var \app\plugins\Plugin $plugin */
                $plugin = new $Class();
                $item->display_name = $plugin->getDisplayName();
            }
            restore_error_handler();
        }
        return $this->list;
    }

    /**
     * @param $name
     * @return bool
     * @throws \Exception
     */
    public function install($name)
    {
        $plugin = $this->getPlugin($name);
        $plugin->beforeInstall();
        $plugin->install();
        $this->saveData($plugin);
        $this->copyAssets($plugin);
        $plugin->afterInstall();
        return true;
    }

    /**
     * 卸载插件
     * @param $name
     * @return bool
     * @throws \Exception
     */
    public function uninstall($name)
    {
        $Plugin = Plugin::findOne([
            'name' => $name,
            'is_delete' => 0,
        ]);
        if (!$Plugin) {
            throw new \Exception('插件不存在或未安装。');
        }
        $plugin = $this->getPlugin($name);
        $plugin->beforeUninstall();
        $Plugin->is_delete = 1;
        if (!$Plugin->save()) {
            throw new \Exception((new Model())->responseErrorMsg($Plugin));
        }
        $plugin->uninstall();
        $plugin->afterUninstall();
        return true;
    }

    /**
     * @param $name
     * @return bool
     * @throws \Exception
     */
    public function update($name)
    {
        $plugin = $this->getPlugin($name);
        $plugin->beforeUpdate();
        $this->copyAssets($plugin);
        $plugin->afterUpdate();
        return true;
    }

    /**
     * @param \app\plugins\Plugin $plugin
     * @return bool
     * @throws \Exception
     */
    private function saveData($plugin)
    {
        $corePlugin = Plugin::findOne(['name' => $plugin->getName()]);
        $versionFile = \Yii::$app->basePath . '/plugins/' . $plugin->getName() . '/version';
        if (file_exists($versionFile)) {
            $version = file_get_contents($versionFile);
            if (!$version || !preg_match('/^\d*(\.\d*)*$/', $version)) {
                $version = '';
            }
        } else {
            $version = '';
        }
        if (!$corePlugin) {
            $corePlugin = new Plugin();
        }
        $corePlugin->name = $plugin->getName();
        $corePlugin->display_name = $plugin->getName();
        $corePlugin->version = $version;
        $corePlugin->is_delete = 0;
        if (!$corePlugin->save()) {
            throw new \Exception('插件安装失败: ' . (new BaseModel())->responseErrorMsg($corePlugin));
        }
        return true;
    }

    /**
     * @param \app\plugins\Plugin $plugin
     * @return bool
     * @throws \Exception
     */
    private function copyAssets($plugin)
    {
        $pluginAssets = \Yii::$app->basePath . '/plugins/' . $plugin->getName() . '/assets';
        if (!is_dir($pluginAssets)) {
            return true;
        }
        $assetsDir = \Yii::$app->basePath . '/web/assets/plugins';
        if (!is_dir($assetsDir)) {
            if (!make_dir($assetsDir)) {
                throw new \Exception($assetsDir . '目录无法创建，请检查目录写入权限。');
            }
        }
        FileHelper::copyDirectory($pluginAssets, $assetsDir . '/' . $plugin->getName());
        return true;
    }

    /**
     * 获取插件的Plugin类
     * @param $name
     * @return \app\plugins\Plugin
     * @throws ClassNotFoundException
     */
    public function getPlugin($name)
    {
        $Class = 'app\\plugins\\' . $name . '\\Plugin';

        if (!class_exists($Class)) {
            throw new ClassNotFoundException('插件`' . $name . '`相关类Plugin不存在。',$Class);
        }
        if (!$this->xPlugins) {
            $this->xPlugins = [];
        }
        if (!empty($this->xPlugins[$name])) {
            return $this->xPlugins[$name];
        }
        $object = new $Class();
        $this->xPlugins[$name] = $object;
        return $this->xPlugins[$name];
    }

    /**
     * @param \app\plugins\Plugin $plugin
     */
    public function setCurrentPlugin($plugin)
    {
        $this->xCurrentPlugin = $plugin;
    }

    //商品详情路径
    public function getGoodsUrl($item)
    {
        return sprintf("/pages/goods/goods?id=%u", $item['goods']['id']);
    }

    /**
     * 获取当前的插件
     * @return \app\plugins\Plugin
     */
    public function getCurrentPlugin()
    {
        return $this->xCurrentPlugin;
    }

    /**
     * 扫描插件目录列表
     * @return \app\plugins\Plugin[]
     * @throws \Exception
     */
    public function scanPluginList()
    {
        $baseDir = \Yii::$app->basePath . '/plugins';
        if (!is_dir($baseDir)) {
            return [];
        }
        $handle = opendir($baseDir);
        if (!$handle) {
            throw new \Exception('无法访问目录`' . $baseDir . '`，请确认该目录是否有访问权限。');
        }
        $list = [];
        while (($file = readdir($handle)) !== false) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }
            if (!is_dir($baseDir . '/' . $file)) {
                continue;
            }
            try {
                $plugin = $this->getPlugin($file);
                $list[] = $plugin;
            } catch (\Exception $e) {
            }
        }

        closedir($handle);
        return $list;
    }

    /**
     * 获取已安装插件
     * @param $name
     * @return Plugin|null
     */
    public function getInstalledPlugin($name)
    {
        return Plugin::find()->where(['name' => $name, 'is_delete' => 0,])->one();
    }

    /**
     * 获取商城头部导航按钮
     */
    public function getHeaderNavs()
    {
        $Plugins = $this->getList();
        $navs = [];
        $userPermissions = \Yii::$app->role->permission;
        foreach ($Plugins as $Plugin) {
            try {
                $plugin = $this->getPlugin($Plugin->name);
            } catch (\Exception $exception) {
                continue;
            }
            if (is_array($userPermissions)) {
                $hasAuth = false;
                if (in_array($plugin->getName(), $userPermissions)) {
                    $hasAuth = true;
                }
                if (!$hasAuth) {
                    continue;
                }
            }
            $nav = $plugin->getHeaderNav();
            if ($nav) {
                $navs[] = $nav;
            }
        }
        return $navs;
    }

    /**
     * @return array
     * 获取所有平台类型的插件例如微信平台、支付宝平台
     */
    public function getAllPlatformPlugins()
    {
        $Plugins = $this->getList();
        $platformPlugin = [];
        foreach ($Plugins as $item) {
            $name = $item->name;
            $Class = 'app\\plugins\\' . $name . '\\Plugin';
            if (!class_exists($Class)) {
                continue;
            }
            /* @var \app\plugins\Plugin $object */
            $object = new $Class();
            if ($object->getIsPlatformPlugin()) {
                $platformPlugin[$name] = $object;
            }
        }
        return $platformPlugin;
    }

    /**
     * @param User $user
     * @return array
     * 获取小程序用户信息
     */
    public function getUserInfo($user)
    {
        $Plugins = $this->getList();
        $result = [];
        foreach ($Plugins as $Plugin) {
            try {
                $plugin = $this->getPlugin($Plugin->name);
            } catch (\Exception $exception) {
                continue;
            }
            $result = array_merge($result, $plugin->getUserInfo($user));
        }
        return $result;
    }

    /**
     * @return array
     * 获取链接
     */
    public function getPickList()
    {
        $result = [];
        $plugins = $this->getList();
        foreach ($plugins as $plugin) {
            $PluginClass = 'app\\plugins\\' . $plugin->name . '\\Plugin';
            /** @var Plugin $pluginObject */
            if (!class_exists($PluginClass)) {
                continue;
            }
            $object = new $PluginClass();
            if (method_exists($object, 'getPickLink')) {
                $result = array_merge($result, $object->getPickLink());
            }
        }
        return $result;
    }

    public static function getPluginBaseAssetsUrl($pluginId = null)
    {
        if (\Yii::$app->request->baseUrl == '/web') {
            $rootUrl = '';
        } else {
            $rootUrl = rtrim(dirname(\Yii::$app->request->baseUrl), '/');
        }
        return \Yii::$app->request->hostInfo . $rootUrl
            . '/plugins/'
            . ($pluginId ? $pluginId : \Yii::$app->controller->module->id) . '/assets';
    }

    public static function getPluginAssetsPath($pluginId = null)
    {
        return \Yii::$app->basePath
            . '/plugins/'
            . ($pluginId ? $pluginId : \Yii::$app->controller->module->id)
            . '/assets';
    }
}
