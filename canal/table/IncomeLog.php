<?php

namespace app\canal\table;

use app\notification\BillAccountCommissionNotification;
use app\notification\GoodsCommissionNotification;
use app\notification\RevenueRecordCommissionNotification;
use app\notification\StoreCommissionNotification;

class IncomeLog
{
    const source_type_value = ['store','checkout','boss','hotel_commission','hotel_3r_commission','goods'];

    public function insert($rows)
    {
        foreach ($rows as $row)
        {
            if (isset($row['flag']) && $row['flag']) {
                if (isset($row['source_type'])) {
                    if ($row['source_type'] == 3) {
                        $row['source_type'] = 'checkout';
                        RevenueRecordCommissionNotification::send($row);
                        \Yii::error('IncomeLogNotice:' . json_encode($row) . '---time:' . date("Y-m-d H:i:s", time()));
                    } elseif ($row['source_type'] == 4){
                        $row['source_type'] = 'store';
                        RevenueRecordCommissionNotification::send($row);
                        \Yii::error('IncomeLogNotice:' . json_encode($row) . '---time:' . date("Y-m-d H:i:s", time()));
                    }
                }
            }
        }
    }

    public function update($mixDatas)
    {
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