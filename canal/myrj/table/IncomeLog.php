<?php

namespace app\canal\myrj\table;

use app\notification\RevenueRecordCommissionNotification;

class IncomeLog
{
    const source_type_value = ['boss','hotel_commission','hotel_3r_commission','goods','area'];
    const add_source_type_key = [3,4,10,11,12,15,16];

    public function insert($rows)
    {
        foreach ($rows as $row)
        {
            if (isset($row['flag']) && $row['flag'] == 1) {
                RevenueRecordCommissionNotification::send($row);
            }
        }
    }

    public function update($mixDatas)
    {
        foreach ($mixDatas as $mixData) {
            $condition = $mixData['condition'];
            $update = $mixData['update'];
            if (isset($update['flag']) && $update['flag'] == 1) {
                $income_log = \app\models\IncomeLog::find()->where($condition)->asArray()->one();
                if ($income_log) {
                    RevenueRecordCommissionNotification::send($income_log);
                    \Yii::error('IncomeLogNotice:' . json_encode($mixData) . '---time:' . date("Y-m-d H:i:s", time()));
                }
            }
        }
    }
}
