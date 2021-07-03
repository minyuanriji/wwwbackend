<?php
/**
 * Created by PhpStorm.
 * User: 阿源
 * Date: 2020/10/24
 * Time: 18:03
 */
namespace app\commands;

use app\forms\mall\data_statistics\TimingStatisticsForm;
use yii\console\Controller;

class StatisticsController extends Controller
{

    //凌晨调用
    public function actionUpdateDay(){
        set_time_limit(0);
        $form = new TimingStatisticsForm();
        $form->updateDay();
    }


    //时间段调用
    public function actionUpdateHour(){
        set_time_limit(0);
        $form = new TimingStatisticsForm();
        $form->updateHour();
    }

}