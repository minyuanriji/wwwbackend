<?php

namespace app\canal\table;

use app\notification\BillAccountCommissionNotification;
use app\notification\GoodsCommissionNotification;
use app\notification\StoreCommissionNotification;

class IncomeLog
{

    public function insert($rows)
    {
        foreach ($rows as $row)
        {
            if (isset($row['flag']) && $row['flag']) {
                if (isset($row['source_type'])) {
                    if ($row['source_type'] == 3) {
                        BillAccountCommissionNotification::send($row);
                    } elseif ($row['source_type'] == 4) {
                        StoreCommissionNotification::send($row);
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
                $income_log = \app\models\IncomeLog::findOne($condition);
                if ($income_log && $income_log->source_type == 'goods') {
                    GoodsCommissionNotification::send($income_log);
                }
            }
        }
    }
}