<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-27
 * Time: 14:13
 */

namespace app\controllers\api;

use app\events\StatisticsEvent;
use app\forms\api\CacheIndexForm;
use app\helpers\APICacheHelper;
use app\models\StatisticsBrowseLog;

use app\component\aiBaidu\lib\AipSpeech;


class IndexController extends ApiController
{
    public function actionIndex()
    {
        $form = new CacheIndexForm();
        $form->attributes = $this->requestData;
        $form->mall_id = \Yii::$app->mall->id;
        $form->is_login = !\Yii::$app->user->isGuest;
        $form->login_uid = $form->is_login ? \Yii::$app->user->id : 0;

        \Yii::$app->trigger(StatisticsBrowseLog::EVEN_STATISTICS_LOG,
            new StatisticsEvent([
                'mall_id'     => \Yii::$app->mall->id,
                'browse_type' => 0,
                'user_id'     => \Yii::$app->user->id,
                'user_ip'     => $_SERVER['REMOTE_ADDR']
            ])
        );

        return $this->asJson(APICacheHelper::get($form));
    }


    //时间段调用
    public function actionUpdateHour(){
        return ['msg'=>'注册成功','access_token' => "",'mobile'=>""];
    }
}