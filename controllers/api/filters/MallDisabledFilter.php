<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 商城禁用过滤器
 * Author: zal
 * Date: 2020-04-17
 * Time: 20:01
 */

namespace app\controllers\api\filters;

use app\core\ApiCode;
use app\models\Admin;
use app\models\AdminInfo;
use yii\base\ActionFilter;

class MallDisabledFilter extends ActionFilter
{
    public function beforeAction($action)
    {
        if (\Yii::$app->mall->is_disable) {
            \Yii::$app->response->data = [
                'code' => ApiCode::CODE_MALL_NOT_EXIST,
                'msg' => '商城被禁用。',
                'data' => [
                    'text' => '该小程序已被禁用'
                ]
            ];
            return false;
        }

        $adminInfo = Admin::findOne(['id' => \Yii::$app->mall->admin_id]);
        if (!$adminInfo || (\Yii::$app->mall->expired_at != '0' && strtotime(\Yii::$app->mall->expired_at) < time())
        || ($adminInfo->expired_at != '0' && strtotime($adminInfo->expired_at) < time())) {
            \Yii::$app->response->data = [
                'code' => ApiCode::CODE_MALL_NOT_EXIST,
                'msg' => '商城已过期。',
                'data' => [
                    'text' => '该小程序已过期'
                ]
            ];
            return false;
        }

        return true;
    }
}
