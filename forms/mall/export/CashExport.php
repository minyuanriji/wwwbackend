<?php

namespace app\forms\mall\export;

use app\core\CsvExport;

class CashExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'user_id',
                'value' => '用户ID',
            ],
            [
                'key' => 'nickname',
                'value' => '用户昵称',
            ],
            [
                'key' => 'mobile',
                'value' => '用户手机号',
            ],
            [
                'key' => 'account_holder',
                'value' => '开户人',
            ],
            [
                'key' => 'bank_no',
                'value' => '银行卡号',
            ],
            [
                'key' => 'bank_deposit',
                'value' => '开户行',
            ],
            [
                'key' => 'apply_money',
                'value' => '申请提现金额',
            ],
            [
                'key' => 'service_charge',
                'value' => '手续费',
            ],
            [
                'key' => 'actual_money',
                'value' => '实际打款金额',
            ],
            [
                'key' => 'type',
                'value' => '状态',
            ],
            [
                'key' => 'created_at',
                'value' => '申请时间',
            ],
            [
                'key' => 'pay_time',
                'value' => '打款时间',
            ],
            [
                'key' => 'remarks',
                'value' => '备注',
            ],
        ];
    }

    public function export($query, $alias = '')
    {
        $orderBy = $alias . 'created_at';
        $list = $query->orderBy($orderBy)->asArray()->all();
        $this->transform($list);
        $this->getFields();
        $dataList = $this->getDataList();

        $fileName = '用户提现记录' . date('YmdHis');
        (new CsvExport())->export($dataList, $this->fieldsNameList, $fileName);
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 1;
        foreach ($list as $item) {
            $arr = [];
            $arr['number'] = $number++;
            $arr['desc'] = $item['desc'];
            $arr['nickname'] = $item['nickname'];
            $arr['user_id'] = $item['uid'];
            $arr['mobile'] = $item['mobile'];
            $arr['money'] = (float)$item['money'];
            $arr['created_at'] = $this->getDateTime($item['created_at']);
            $arr['type'] = $item['type'] == 1 ? '收入' : '支出';
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }
}
