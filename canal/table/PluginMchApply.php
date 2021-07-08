<?php

namespace app\canal\table;

use app\notification\MchApplyAdoptNotification;
use app\notification\MchApplyNoPassNotification;
use app\plugins\mch\models\MchApply;

class PluginMchApply
{

    public function insert($rows)
    {
    }

    public function update($mixDatas)
    {
        foreach ($mixDatas as $mixData) {
            $condition = $mixData['condition'];
            $update = $mixData['update'];
            if (isset($update['status'])) {
                $mch_apply = MchApply::find()->where($condition)->one();
                if ($mch_apply) {
                    if ($update['status'] == MchApply::STATUS_PASSED) {
                        MchApplyAdoptNotification::send($mch_apply);
                    } elseif ($update['status'] == MchApply::STATUS_REFUSED) {
                        MchApplyNoPassNotification::send($mch_apply);
                    }
                }
            }
        }
    }
}
