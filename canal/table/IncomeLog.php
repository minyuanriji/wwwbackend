<?php

namespace app\canal\table;

use app\notification\BillAccountCommissionNotification;
use app\notification\GoodsCommissionNotification;
use app\notification\StoreCommissionNotification;

class IncomeLog
{

    public function insert($rows)
    {
    }

    public function update($mixDatas)
    {
        foreach ($mixDatas as $mixData) {
            $condition = $mixData['condition'];
            $update = $mixData['update'];
            if (isset($update['flag']) && $update['flag']) {
                $income_log = \app\models\IncomeLog::findOne($condition);
                if ($income_log) {
                    switch ($income_log->source_type)
                    {
                        case 'checkout';
                            BillAccountCommissionNotification::send($income_log);
                            break;
                        case 'store';
                            StoreCommissionNotification::send($income_log);
                            break;
                        case 'goods';
                            GoodsCommissionNotification::send($income_log);
                            break;
                        default:
                            return;
                    }
                }
            }
        }
    }
}