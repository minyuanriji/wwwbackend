<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-29
 * Time: 9:10
 */

namespace app\controllers\api;


use app\events\StatisticsEvent;
use app\forms\api\cat\CatListForm;
use app\helpers\APICacheHelper;
use app\models\StatisticsBrowseLog;

class CatController extends ApiController
{
    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-29
     * @Time: 9:10
     * @Note:分类列表
     */
    public function actionList()
    {
        $form = new CatListForm();
        $form->attributes = $this->requestData;
        $form->mall_id = \Yii::$app->mall->id;

        \Yii::$app->trigger(StatisticsBrowseLog::EVEN_STATISTICS_LOG,
            new StatisticsEvent([
                'mall_id'     => \Yii::$app->mall->id,
                'browse_type' => 1,
                'user_id'     => \Yii::$app->user->id,
                'user_ip'     => $_SERVER['REMOTE_ADDR']
            ])
        );

        return $this->asJson(APICacheHelper::get($form));
    }
}