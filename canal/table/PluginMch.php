<?php

namespace app\canal\table;

use app\notification\MchApplyPassedNotification;
use app\plugins\mch\models\Mch;
use Yii;

class PluginMch
{

    public function insert($rows)
    {

    }

<<<<<<< HEAD
    public function update($mixDatas){
        foreach($mixDatas as $mixData){
            $condition = $mixData['condition'];
            $update = $mixData['update'];
            if(isset($update['review_status'])){
                if($update['review_status'] == Mch::REVIEW_STATUS_CHECKED){
=======
    public function update($mixDatas)
    {
        foreach ($mixDatas as $mixData) {
            $condition = $mixData['condition'];
            $update = $mixData['update'];
            if (isset($update['review_status'])) {
                if ($update['review_status'] > Mch::REVIEW_STATUS_UNCHECKED) {
>>>>>>> 5f84071819a097f8f1ffaa3c50a6b111850baabe
                    $mch = Mch::find()->where($condition)->one();
                    $mch && MchApplyPassedNotification::send($mch);
                }
            }
        }
    }
}