<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 黑名单过滤器
 * Author: zal
 * Date: 2020-04-17
 * Time: 20:01
 */

namespace app\controllers\api\filters;

use app\core\ApiCode;
use app\models\User;
use yii\base\ActionFilter;

class BlackListFilter extends ActionFilter
{
    private $routeList = [
        'api/order/preview',
        'api/index/index',
    ];

    public function beforeAction($action)
    {
        /** @var User $user */
        $user = User::findOne(['id' => \Yii::$app->user->id]);
        if ($user && $user->is_blacklist) {
            \Yii::$app->response->data = [
                'code' => ApiCode::BLACKLIST_CODE_FAIL,
                'msg' => '您已被限制任何操作，如有疑问请联系客服！',
            ];
            return false;
            $plugins = \Yii::$app->plugin->list;
            foreach ($plugins as $plugin) {
                $PluginClass = 'app\\plugins\\' . $plugin->name . '\\Plugin';
                /** @var Plugin $pluginObject */
                if (!class_exists($PluginClass)) {
                    continue;
                }
                $object = new $PluginClass();
                if (method_exists($object, 'getBlackList')) {
                    $routeList = array_merge($this->routeList, $object->getBlackList());
                    $this->routeList = $routeList;
                }
            }

            // 黑名单用户无法访问相关路由
            if (in_array(\Yii::$app->requestedRoute, $this->routeList)) {
                \Yii::$app->response->data = [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '您已被限制该操作',
                ];
                return false;
            }
        }

        return true;
    }
}
