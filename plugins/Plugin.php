<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 插件核心类
 * Author: zal
 * Date: 2020-04-13
 * Time: 16:50
 */


namespace app\plugins;


use app\forms\OrderConfig;
use app\handlers\orderHandler\OrderCanceledHandlerClass;
use app\handlers\orderHandler\OrderChangePriceHandlerClass;
use app\handlers\orderHandler\OrderCreatedHandlerClass;
use app\handlers\orderHandler\OrderPayedHandlerClass;
use app\handlers\orderHandler\OrderSalesHandlerClass;
use app\helpers\sms\StockFillMessage;
use app\helpers\sms\Sms;
use app\logic\CommonLogic;
use app\models\Goods;
use app\models\User;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;

abstract class Plugin
{
    protected static $instance;

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    abstract public function getName();
    abstract public function getLogo();
    abstract public function getPriceTypeName($price_log_id=0);



    /**
     * 插件显示名称
     * @return string
     */
    abstract public function getDisplayName();

    /**
     * 插件安装执行代码
     * @return mixed
     */
    public function install()
    {
        return true;
    }

    /**
     * 插件更新执行代码
     * @return mixed
     */
    public function update()
    {
        return true;
    }

    /**
     * 插件卸载执行代码
     * @return mixed
     */
    public function uninstall()
    {
        return true;
    }

    /**
     * 插件安装之前
     */
    public function beforeInstall()
    {
    }

    /**
     * 插件安装之后
     */
    public function afterInstall()
    {
    }

    /**
     * 插件更新之前
     */
    public function beforeUpdate()
    {
    }

    /**
     * 插件更新之后
     */
    public function afterUpdate()
    {
    }

    /**
     * 插件卸载之前
     */
    public function beforeUninstall()
    {
    }

    /**
     * 插件卸载之后
     */
    public function afterUninstall()
    {
    }





    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-07
     * @Time: 15:08
     * @Note:获取是否设置快速访问菜单
     */
    public function getIsSetToQuickMenu()
    {
        return false;
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-07
     * @Time: 14:31
     * @Note:为主菜单提供数据
     */
    public function getMenuForMainMenu()
    {

        return [];
    }


    /**
     * 获取插件菜单列表
     * @return array
     */
    public function getMenus()
    {
        return [];
    }

    public function handler()
    {
    }

    /**
     * 插件的小程序端配置，小程序端可使用getApp().config(e => { e.plugin.xxx });获取配置，xxx为插件唯一id
     * @return array
     */
    public function getAppConfig()
    {
        return [];
    }

    /**
     * 获取插件入口路由
     * @return string|null
     */
    public function getIndexRoute()
    {
        return null;
    }

    /**
     * @return string 获取插件图标
     */
    public function getIconUrl()
    {
        $default = \Yii::$app->request->getBaseUrl() . '/statics/img/common/unknown-plugin-icon.png';
        $fileName = $this->getName() . '/icon.png';
        if (file_exists(\Yii::$app->basePath . '/plugins/' . $fileName)) {
            if (\Yii::$app->request->baseUrl == '/web') {
                $baseUrl = '';
            } else {
                $baseUrl = dirname(\Yii::$app->request->baseUrl);
                $baseUrl = rtrim($baseUrl, '/');
            }
            $url = $baseUrl . '/plugins/' . $fileName;
            return $url ? $url : $default;
        } else {
            return $default;
        }
    }

    /**
     * @return string 获取插件的详细描述。
     */
    public function getContent()
    {
        return '';
    }

    /**
     * @return false|string|null
     */
    public function getVersionFileContent()
    {
        $versionFile = \Yii::$app->basePath . '/plugins/' . static::getName() . '/version';
        if (file_exists($versionFile)) {
            return file_get_contents($versionFile);
        }
        return null;
    }

    /**
     * 插件可共用的跳转链接
     * @return array
     */
    public function getPickLink()
    {
        return [];
    }

    /**
     * 插件可设置的转发信息的页面链接
     */
    public function getShareContentSetting()
    {
        return [];
    }

    /**
     * 插件可设置标题的页面链接
     */
    public function getPageTitle()
    {
        return [];
    }

    /**
     * 插件可用于展示页面信息
     * @return array
     */
    public function getShowPageInfo()
    {
        return [];
    }

    /**
     * 获取商城顶部导航按钮
     * @return null|array 返回格式: ['name'=>'名称','url'=>'链接','new_window'=>'true|false, 是否新窗口打开']
     */
    public function getHeaderNav()
    {
        return null;
    }

    /**
     * @return OrderConfig
     * @throws \Exception
     * 获取插件的相关配置 例如订单是否分销、是否短信提醒、是否邮件提醒、是否小票打印等
     */
    public function getOrderConfig()
    {
        return new OrderConfig();
    }

    /**
     * @return bool
     * 判断是否是平台 例如微信平台，支付宝平台
     */
    public function getIsPlatformPlugin()
    {
        return false;
    }

    /**
     * @return OrderPayedHandlerClass
     * 重改订单支付完成事件
     */
    public function getOrderPayedHandleClass()
    {
        return new OrderPayedHandlerClass();
    }

    /**
     * @return OrderCreatedHandlerClass
     * 重改订单创建事件
     */
    public function getOrderCreatedHandleClass()
    {
        return new OrderCreatedHandlerClass();
    }

    /**
     * @return OrderCanceledHandlerClass
     * 重改订单取消事件
     */
    public function getOrderCanceledHandleClass()
    {
        return new OrderCanceledHandlerClass();
    }

    /**
     * @return OrderSalesHandlerClass
     * 重改订单售后事件
     */
    public function getOrderSalesHandleClass()
    {
        return new OrderSalesHandlerClass();
    }

    /* @return OrderChangePriceHandlerClass
     * 重改订单创建事件
     */
    public function getOrderChangePriceHandlerClass()
    {
        return new OrderChangePriceHandlerClass();
    }

    /**
     * @param string $type mall--后台数据|api--前端数据
     * @return null
     * @throws \Exception
     * 获取首页布局数据
     */
    public function getHomePage($type)
    {
        return null;
    }

    /**
     * @throws \Exception
     * @return bool
     * 初始化统计数据
     */
    public function initData()
    {
        return true;
    }


    /**
     * 黑名单 路由
     * @return array
     */
    public function getBlackList()
    {
        return [];
    }

    /**
     * @param User $user
     * @return array
     */
    public function getUserInfo($user)
    {
        return [];
    }

    /**
     * @return array
     * 获取统计菜单
     */
    public function getStatisticsMenus()
    {
        return [];
    }

    public function getSignCondition($where)
    {
        return false;
    }

    public function templateSender()
    {
        throw new \Exception('暂不支持模板消息发送');
        return null;
    }

    public function getOrderInfo($orderId)
    {
        return [];
    }

    /**
     * @return array
     * 不支持的功能
     */
    public function getNotSupport()
    {
        return [];
    }

    /**
     * @param Goods $goods
     * @return array
     * 商品列表商品额外的信息
     */
    public function getGoodsExtra($goods)
    {
        return [];
    }

    /**
     * @return array
     * 模板消息发送的列表
     */
    public function templateList()
    {
        return [];
    }

    public function updateGoodsPrice($goods)
    {
        return true;
    }

    /**
     * 发送短信通知
     * @param array $mobile [157.., 183..]
     * @param $message 消息对象
     * @param $content
     * @param Sms $sms
     * @return bool
     * @throws NoGatewayAvailableException
     * @throws \Exception
     */
    public function sendSms($mobile, $message,$sms,$content)
    {
        if(is_array($mobile)){
            if (count($mobile) != count($mobile, 1)) {
                throw new \Exception('手机号数组格式错误,请传入一维数组');
            }
        }
        \Yii::warning('plugin sendSms start');
        try {
            if(is_array($mobile)){
                foreach ($mobile as $item) {
                    $mobile = (string)$item;
                    $sms->easySms->send($mobile, $message);
                    $sms->saveValidateCodeLog($mobile, $content);
                }
            }else{
                \Yii::warning('plugin sendSms 短信发送:' ."mobile:".$mobile);
                $sms->easySms->send($mobile, $message);
                $res = $sms->saveValidateCodeLog($mobile, $content);
                \Yii::warning('plugin sendSms res:' .$res);
            }
            return true;
        } catch (\Exception $e) {
            \Yii::error('plugin sendSms 短信发送失败:' . CommonLogic::getExceptionMessage($e));
            return false;
            //throw $e;
        }
    }
}
