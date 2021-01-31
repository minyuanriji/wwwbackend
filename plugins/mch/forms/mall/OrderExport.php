<?php

namespace app\plugins\mch\forms\mall;


class OrderExport extends \app\forms\mall\export\OrderExport
{

    public function fieldsList()
    {
        $exportFields = parent::fieldsList();
        foreach ($exportFields as $key =>  $item) {
            if ($item['key'] == 'city_name' || $item['key'] == 'city_mobile') {
                unset($exportFields[$key]);
            }
        }
        return array_values($exportFields);
    }

    public function getFileName()
    {
        if ($this->send_type == 1) {
            $name = '多商户-自提订单';
        } elseif ($this->send_type == 2) {
            $name = '多商户-同城配送';
        } else {
            $name = '多商户-订单列表';
        }
        $fileName = $name . date('YmdHis');

        return $fileName;
    }
}
