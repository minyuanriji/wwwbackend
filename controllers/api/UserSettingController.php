<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/10/19
 * Time: 10:46
 */

namespace app\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\models\UserSetting;

class UserSettingController extends ApiController
{

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class'  => LoginFilter::class,
                'ignore' => ['']
            ],
        ]);
    }

    /**
     * 更新用户设置
     */
    public function actionUpdate()
    {
        $params            = $this->requestData;
        $params['user_id'] = \Yii::$app->user->id;
        $params['mall_id'] = \Yii::$app->mall->id;
        $UserSetting       = new UserSetting();
        $this->asJson($UserSetting->store($params));
    }

    /**
     * 获取用户设置
     */
    public function actionGetOne()
    {
        $params            = $this->requestData;
        $params['user_id'] = \Yii::$app->user->id;
        $params['mall_id'] = \Yii::$app->mall->id;
        $UserSetting       = new UserSetting();
        $this->asJson($UserSetting->getOne($params));
    }
}