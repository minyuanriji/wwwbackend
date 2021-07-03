<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-01
 * Time: 21:32
 */

namespace app\core;

use app\core\cloud\Cloud;
use app\core\currency\Currency;
use app\core\exceptions\ClassNotFoundException;
use app\core\kdOrder\KdOrder;
use app\core\payment\Payment;
use app\core\sms\Sms;
use app\forms\admin\permission\branch\IndBranch;
use app\forms\admin\permission\role\AdminRole;
use app\forms\admin\permission\role\BaseRole;
use app\forms\admin\permission\branch\BaseBranch;
use app\forms\admin\permission\role\MchRole;
use app\forms\admin\permission\role\OperatorRole;
use app\forms\admin\permission\role\SuperAdminRole;
use app\handlers\BaseHandler;
use app\handlers\HandlerRegister;
use app\helpers\PluginHelper;
use app\helpers\SerializeHelper;
use app\logic\AdminLogic;
use app\models\Admin;
use app\models\Mall;
use app\models\User;
use Dotenv\Dotenv;
use yii\base\Module;
use yii\queue\Queue;
use yii\redis\Connection;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Trait Application
 * @package app\core
 * @property Response $response
 */

/**
 * Trait Application
 * @package app\core
 * @property PluginHelper $plugin
 * @property SerializeHelper $serializer
 * @property integer $mallId
 * @property Mall $mall
 * @property integer $mchId
 * @property string $appPlatform
 * @property string $source
 * @property string $appVersion
 * @property Payment $payment
 * @property Cloud $cloud
 * @property Connection $redis
 * @property Queue $queue
 * @property Currency $currency
 * @property AppMessage $appMessage
 * @property Sms $sms
 * @property $alipay
 * @property BaseRole $role
 * @property BaseBranch $branch
 * @property string $userIp
 */
trait Application
{
    /** @var Mall $mall */
    private $mall;
    protected $mchId;
    private $appPlatform;
    private $source;
    /** @var Payment $payment */
    private $payment;
    private $xCloud;
    private $currency;
    private $kdOrder;
    private $appMessage;
    private $sms;
    private $alipay;
    private $role;
    private $branch;
    private $appVersion;
    private $mallId;
    private $userIp;

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-01
     * @Time: 21:54
     * @Note:定义环境常量
     * @return $this
     *
     */
    protected function defineConstants()
    {
        define_once('IN_IA', true);
        define_once('APP_PLATFORM_MP_WX', 'mp-wx');
        define_once('APP_PLATFORM_MP_ALI', 'mp-ali');
        define_once('APP_PLATFORM_MP_BD', 'mp-bd');
        define_once('APP_PLATFORM_MP_TT', 'mp-tt');
        define_once('APP_PLATFORM_MP_WECHAT', 'wechat');
        define_once('APP_PLATFORM_H5', 'h5');
        $this->defineEnvConstants(['YII_DEBUG', 'YII_ENV']);
        return $this;
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-01
     * @Time: 21:47
     * @Note: 定义环境常量
     * @param array $names
     * @return $this
     */
    protected function defineEnvConstants($names = [])
    {
        foreach ($names as $name) {
            if ((!defined($name)) && ($value = env($name))) {
                define($name, $value);
            }
        }
        return $this;
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-01
     * @Time: 21:35
     * @Note: 将对象或数组以json格式返回
     * @return $this
     *
     */
    protected function responseAsJson()
    {
        $this->response->on(
            Response::EVENT_BEFORE_SEND,
            function ($event) {
                /** @var \yii\web\Response $response */
                $response = $event->sender;
                if (is_array($response->data) || is_object($response->data)) {
                    $response->format = \yii\web\Response::FORMAT_JSON;
                }
            }
        );
        return $this;
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-01
     * @Time: 22:03
     * @Note: 加载环境配置文件
     * @return $this
     */
    protected function loadDotEnv()
    {
        //如果设置了dev后，那么YII_ENV_DEV就会为true
        try {
            $dotEnv = new \Dotenv\Dotenv(dirname(__DIR__));
            $dotEnv->load();
        } catch (\Dotenv\Exception\InvalidPathException $ex) {

        }
        return $this;
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-02
     * @Time: 13:16
     * @Note:注册全局事件
     * @return $this
     */
    protected function registerHandlers()
    {
        $register = new HandlerRegister();
        $handlerClasses = $register->getHandlers();
        foreach ($handlerClasses as $handlerClass) {
            /**
             * @var BaseHandler $handler
             */
            $handler = new $handlerClass();
            if ($handler instanceof BaseHandler) {
                $handler->register();
            }
        }
        return $this;
    }


    private $loadedPluginComponents = [];

    /**
     * @Author: 广东七件事 ganxiaohao
     * Date: 2020-05-10
     * Time: 22:00
     * @Note: 加载插件内的组件
     * @param $component
     * @param null $componentPath
     * @param bool $onceLoad
     */
    public function loadPluginComponentView($component, $onceLoad = true)
    {

        $plugin=\Yii::$app->plugin->currentPlugin->getName();
        $componentPath = \Yii::$app->basePath . "/plugins/{$plugin}/views/components";
        $component_file = "{$componentPath}/{$component}.php";
        if (isset($this->loadedPluginComponents[$component_file]) && $onceLoad) {
            //此组件已经载入过了
            return;
        }
        $this->loadedPluginComponents[$component_file] = true;

        //输出页面
        echo $this->getView()->renderFile($component_file) . "\n";
    }


    private $loadedComponents = [];

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-06
     * @Time: 13:25
     * @Note: 载入组件
     * @param string $component //组件名称
     * @param string $componentPath //组件路径
     * @param bool $onceLoad //一次加载
     */
    public function loadComponentView($component, $componentPath = null, $onceLoad = true)
    {
        if (!$componentPath) {
            $componentPath = \Yii::$app->viewPath . '/components';
        }
        
        $component_file = "{$componentPath}/{$component}.php";
        //var_dump($component_file);exit;
        if (isset($this->loadedComponents[$component_file]) && $onceLoad) {
            //此组件已经载入过了
            return;
        }
        $this->loadedComponents[$component_file] = true;

        //输出页面
        echo $this->getView()->renderFile($component_file) . "\n";
    }

    public function createForm($class)
    {
        if (!is_string($class)) {
            throw new \Exception("{$class}不是有效的Class");
        }
        return new $class();
    }

    /**
     * 获取登录用户的角色
     * @Author: 广东七件事 zal
     * @Date: 2020-04-06
     * @Time: 10:25
     * @return BaseRole
     * @throws \Exception
     */
    public function getRole()
    {
        if (!$this->role) {
            if (\Yii::$app->admin->isGuest) {
                throw new \Exception('用户未登录');
            }
            /* @var AdminInfo $adminInfo */
            $adminInfo = AdminLogic::getAdminInfo();
            /* @var Admin $admin */
            $admin = \Yii::$app->admin->identity;
            $config = [
                'adminInfo' => $adminInfo,
                'admin' => $admin,
                'mall' => \Yii::$app->mall
            ];
            if ($admin->admin_type == Admin::ADMIN_TYPE_SUPER) {
                //超级管理员
                $this->role = new SuperAdminRole($config);
            } elseif ($admin->admin_type == Admin::ADMIN_TYPE_ADMIN) {
                //管理员
                $this->role = new AdminRole($config);
            } elseif ($admin->admin_type == Admin::ADMIN_TYPE_OPERATE) {
                //操作员
                $this->role = new OperatorRole($config);
            } elseif ($admin->mch_id > 0) {
                //商户
                $this->role = new MchRole();
            } else {
                throw new \Exception('未知用户权限');
            }
        }
        return $this->role;
    }

    /**
     * 设置登录用户的角色
     * @Author: 广东七件事 zal
     * @Date: 2020-04-10
     * @Time: 10:25
     * @param $role
     * @return BaseRole
     * @throws \Exception
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * 获取登录商城的分支版本
     * @Author: 广东七件事 zal
     * @Date: 2020-04-10
     * @Time: 10:25
     * @return BaseBranch
     */
    public function getBranch()
    {
        if (!$this->branch) {
            $this->branch = new IndBranch();
        }
        return $this->branch;
    }

    /**
     * 获取登录商城id
     * @Author: 广东七件事 zal
     * @Date: 2020-04-10
     * @Time: 10:25
     * @return mixed
     */
    public function getMallId()
    {
        return $this->mallId;
    }

    /**
     * 设置登录商城id
     * @Author: 广东七件事 zal
     * @Date: 2020-04-10
     * @Time: 10:25
     * @params string @mallId
     * @return mixed
     */
    public function setMallId($mallId)
    {
        $this->mallId = $mallId;
    }

    /**
     * 获取登录商城
     * @Author: 广东七件事 zal
     * @Date: 2020-04-10
     * @Time: 10:25
     * @return Mall
     * @throws \Exception
     */
    public function getMall()
    {
        if (!$this->mall || !$this->mall->id) {
            throw new \Exception('mall is Null');
        }
        return $this->mall;
    }

    /**
     * 设置登录商城
     * @Author: 广东七件事 zal
     * @Date: 2020-04-10
     * @Time: 10:25
     * @param Mall $mall
     * @return Mall
     */
    public function setMall(Mall $mall)
    {
        $this->mall = $mall;
    }

    /**
     * 获取系统来源平台
     * @Author: 广东七件事 zal
     * @Date: 2020-04-14
     * @Time: 10:25
     * @return mixed|string
     */
    public function getAppPlatform()
    {
        if ($this->appPlatform) {
            return $this->appPlatform;
        }
        if (!empty(\Yii::$app->request->headers['x-app-platform'])) {
            $this->appPlatform = \Yii::$app->request->headers['x-app-platform'];
            if ($this->appPlatform == 'wx') {
                $this->appPlatform = 'wxapp';
            }
        }
        if (!$this->appPlatform) {
            $this->appPlatform = User::PLATFORM_WECHAT;
        }
        return $this->appPlatform;
    }

    /**
     * 设置系统来源平台
     * @Author: 广东七件事 zal
     * @Date: 2020-04-14
     * @Time: 10:25
     * @param $xAppPlatform
     * @return mixed|string
     */
    public function setAppPlatform($xAppPlatform)
    {
        $this->appPlatform = $xAppPlatform;
    }

    /**
     * 获取系统来源平台
     * @Author: 广东七件事 zal
     * @Date: 2020-04-14
     * @Time: 10:25
     * @return mixed|string
     */
    public function getSource()
    {
        if ($this->source) {
            return $this->source;
        }
        if (!empty(\Yii::$app->request->headers['x-source'])) {
            $this->source = \Yii::$app->request->headers['x-source'];
        }
        if (!$this->source) {
            $this->source = 0;
        }
        return $this->source;
    }

    /**
     * 设置用户分享来源
     * @Author: 广东七件事 zal
     * @Date: 2020-04-14
     * @Time: 10:25
     * @param $xSource
     * @return mixed|string
     */
    public function setSource($xSource)
    {
        $this->appPlatform = $xSource;
    }

    /**
     * @return AppMessage
     */
    public function getAppMessage()
    {
        if (!$this->appMessage) {
            $this->appMessage = new AppMessage();
        }
        return $this->appMessage;
    }

    public function getSms()
    {
        if (!$this->sms) {
            $this->sms = new Sms();
        }
        return $this->sms;
    }

    public function getAlipay()
    {
        throw new \Exception('尚未支持此功能。');
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-15
     * @Time: 17:11
     * @Note:多商户ID
     * @return int
     */
    public function getMchId()
    {
        return $this->mchId ?: 0;
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-15
     * @Time: 17:11
     * @Note:设置多商户ID
     * @param $mchId
     */
    public function setMchId($mchId)
    {
        $this->mchId = $mchId;
    }

    /**
     * 获取支付类
     * @Author: 广东七件事 zal
     * @Date: 2020-04-15
     * @Time: 17:11
     * @return Payment
     */
    public function getPayment()
    {
        if ($this->payment) {
            return $this->payment;
        }
        $this->payment = new Payment();
        return $this->payment;
    }

    /**
     * @return Cloud
     */
    public function getCloud()
    {
        if ($this->xCloud) {
            return $this->xCloud;
        }
        $this->xCloud = new Cloud();
        return $this->xCloud;
    }

    public function getCurrency()
    {
        if ($this->currency) {
            return $this->currency;
        }
        $this->currency = new Currency();
        return $this->currency;
    }

    public function getKdOrder()
    {
        if ($this->kdOrder) {
            return $this->kdOrder;
        }
        $this->kdOrder = new KdOrder();
        return $this->kdOrder;
    }

    /**
     * 加载插件主程序
     * @return $this
     * @throws \Exception
     */
    protected function loadAppPlugins()
    {
        if (!\Yii::$app->db->username) {
            return $this;
        }
        $corePluginTableName = \Yii::$app->db->tablePrefix . 'plugin';
        if (!table_exists($corePluginTableName)) {
            return $this;
        }
        $corePlugins = \Yii::$app->plugin->list;
        foreach ($corePlugins as $corePlugin) {
            try {
                $plugin = \Yii::$app->plugin->getPlugin($corePlugin->name);
                $plugin->handler();
            } catch (ClassNotFoundException $exception) {
                continue;
            }
        }
        return $this;
    }

    /**
     * 加载系统日志类
     * @return $this
     */
    protected function loadAppLogger()
    {
        return $this;
    }

    /**
     * 加载错误提示信息
     *
     * @return self
     */
    protected function loadErrorReporting()
    {
        if (YII_DEBUG) {
            error_reporting(E_ALL);
        } else {
            error_reporting(E_ALL ^ E_NOTICE);
        }
        return $this;
    }

    /**
     * 重写runAction方法使之可运行插件代码
     *
     * @param string $route
     * @param array $params
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\InvalidRouteException
     * @throws \Exception
     */
    public function runAction($route, $params = [])
    {
        bcscale(2);//配置BC函数小数精度

        $route = ltrim($route, '/');
        $pattern = '/^plugin\/.*/';
        preg_match($pattern, $route, $matches);
        if ($matches) {
            $originRoute = $matches[0];
            $originRouteArray = mb_split('/', $originRoute);

            $pluginId = !empty($originRouteArray[1]) ? $originRouteArray[1] : null;
            if (!$pluginId) {
                throw new NotFoundHttpException();
            }
            if (!$this->plugin->getInstalledPlugin($pluginId)) {
                throw new NotFoundHttpException();
            }
            $controllerId = 'index';
            $controllerClass = "app\\plugins\\{$pluginId}\\controllers\\IndexController";
            $actionId = 'index';
            $appendNamespace = '';
            for ($i = 2; $i < count($originRouteArray); $i++) {
                $controllerId = !empty($originRouteArray[$i]) ? $originRouteArray[$i] : 'index';
                $controllerName = preg_replace_callback('/\-./', function ($e) {
                    return ucfirst(trim($e[0], '-'));
                }, $controllerId);
                $controllerName = ucfirst($controllerName);
                $controllerName .= 'Controller';
                $controllerClass = "app\\plugins\\{$pluginId}\\controllers\\{$appendNamespace}{$controllerName}";
                $actionId = !empty($originRouteArray[$i + 1]) ? $originRouteArray[$i + 1] : 'index';
                if (class_exists($controllerClass)) {
                    break;
                }
                $appendNamespace .= $originRouteArray[$i] . '\\';
            }

            try {
                /** @var Controller $controller */
                $controller = \Yii::createObject($controllerClass, [$controllerId, $this]);
                $module = new Module($pluginId, $this);
                $controller->module = $module;
                $this->controller = $controller;
                \Yii::$app->plugin->setCurrentPlugin(\Yii::$app->plugin->getPlugin($pluginId));


                return $controller->runAction($actionId, $params);
            } catch (\ReflectionException $e) {
                throw new NotFoundHttpException(\Yii::t('yii', 'Page not found.'), 0, $e);
            }
        }
        return parent::runAction($route, $params);
    }

    public function getAppVersion()
    {
        if ($this->appVersion) {
            return $this->appVersion;
        }
        if (!empty(\Yii::$app->request->headers['x-app-version'])) {
            $this->appVersion = \Yii::$app->request->headers['x-app-version'];
        }
        if (!$this->appVersion) {
            $this->appVersion = '1.0.0';
        }
        return $this->appVersion;
    }

    public function setAppVersion($appVersion)
    {
        $this->appVersion = $appVersion;
    }

    public function getUserIp() {
        switch (true) {
            case isset($_SERVER["HTTP_X_FORWARDED_FOR"]):
                $this->userIp = $_SERVER["HTTP_X_FORWARDED_FOR"];
                break;
            case isset($_SERVER["HTTP_CLIENT_IP"]):
                $this->userIp = $_SERVER["HTTP_CLIENT_IP"];
                break;
            default:
                $this->userIp = $_SERVER["REMOTE_ADDR"] ? $_SERVER["REMOTE_ADDR"] : '127.0.0.1';
        }
        if (strpos($this->userIp, ', ') > 0) {
            $ips = explode(', ', $this->userIp);
            $this->userIp = $ips[0];
        }
    }

    public function setUserIp($ip)
    {
        $this->userIp = $ip;
    }
}