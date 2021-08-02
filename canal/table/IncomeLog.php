<?php

namespace app\canal\table;

use app\notification\RevenueRecordCommissionNotification;

class IncomeLog
{
    const source_type_value = ['boss','hotel_commission','hotel_3r_commission','goods'];
    const add_source_type_key = [3,4,10,11,12];

    public function insert($rows)
    {
print_r($rows);die;
        foreach ($rows as $row)
        {
            if (isset($row['flag']) && $row['flag']) {
                if (isset($row['source_type'])) {
                    if (in_array($row['source_type'], self::add_source_type_key)) {
                        if ($row['source_type'] == 3) {
                            $row['source_type'] = 'checkout';
                        } elseif ($row['source_type'] == 4){
                            $row['source_type'] = 'store';
                        } elseif ($row['source_type'] == 10){
                            $row['source_type'] = 'addcredit';
                        } elseif ($row['source_type'] == 11){
                            $row['source_type'] = 'addcredit_3r';
                        } elseif ($row['source_type'] == 12){
                            $row['source_type'] = 'giftpacks_commission';
                        }
                        RevenueRecordCommissionNotification::send($row);
                        \Yii::error('IncomeLogNotice:' . json_encode($row) . '---time:' . date("Y-m-d H:i:s", time()));
                    }
                }
            }
        }
    }

    public function update($mixDatas)
    {
print_r($mixDatas);die;
        foreach ($mixDatas as $mixData) {
            $condition = $mixData['condition'];
            $update = $mixData['update'];
            if (isset($update['flag']) && $update['flag']) {
                $income_log = \app\models\IncomeLog::find()->where($condition)->asArray()->one();
                if ($income_log && in_array($income_log['source_type'],self::source_type_value)) {
                    RevenueRecordCommissionNotification::send($income_log);
                    \Yii::error('IncomeLogNotice:' . json_encode($mixData) . '---time:' . date("Y-m-d H:i:s", time()));
                }
            }
        }
    }
}
