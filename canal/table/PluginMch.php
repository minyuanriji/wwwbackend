<?php
namespace app\canal\table;

use app\notification\MchApplyPassedNotification;
use app\plugins\mch\models\Mch;
use Yii;

class PluginMch{

    public function insert($rows){

    }

    public function update($mixDatas){
        foreach($mixDatas as $mixData){
            $condition = $mixData['condition'];
            $update = $mixData['update'];
            if(isset($update['review_status'])){
                if($update['review_status'] == Mch::REVIEW_STATUS_CHECKED){
                    $mch = Mch::find()->where($condition)->one();
                    $mch && MchApplyPassedNotification::send($mch);
                }
            }
        }
    }
}