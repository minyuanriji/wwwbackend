<?php

/* 废弃 2021/07/29
 *
 * */
namespace app\canal\table;

use app\notification\MchApplyAdoptNotification;
use app\notification\MchApplyNoPassNotification;
use app\plugins\mch\models\Mch;

class PluginMch
{

    public function insert($rows)
    {
    }

    public function update($mixDatas)
    {
        /*foreach ($mixDatas as $mixData) {
            $condition = $mixData['condition'];
            $update = $mixData['update'];
            if (isset($update['review_status'])) {
                $mch = Mch::find()->where($condition)->one();
                if ($mch) {
                    if ($update['review_status'] == Mch::REVIEW_STATUS_CHECKED) {
                        MchApplyAdoptNotification::send($mch);
                    } elseif ($update['review_status'] == Mch::REVIEW_STATUS_NOTPASS) {
                        MchApplyNoPassNotification::send($mch);
                    }
                }
            }
        }*/
    }
}
