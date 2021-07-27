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
                $mch_apply = MchApply::findOne($condition);
                if ($mch_apply) {
                    if ($update['status'] == 2) {
                        MchApplyAdoptNotification::send($mch_apply);
                    } elseif ($update['status'] == 1) {
                        MchApplyNoPassNotification::send($mch_apply);
                    }
                }
            }
        }
    }
}
