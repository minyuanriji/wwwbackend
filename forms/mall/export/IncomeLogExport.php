<?php

namespace app\forms\mall\export;

use app\core\CsvExport;

class IncomeLogExport extends BaseExport
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
                'key' => 'type',
                'value' => '金额类型',
            ],
            [
                'key' => 'money',
                'value' => '金额',
            ],
            [
                'key' => 'created_at',
                'value' => '支付日期',
            ],
            [
                'key' => 'desc',
                'value' => '说明',
            ],
        ];
    }

    public function export($query, $alias = '')
    {
        $orderBy = $alias . 'created_at';
        $list = $query->select(['b.*','u.id as uid','u.nickname','u.mobile'])->orderBy($orderBy)->asArray()->all();
        $this->transform($list);
        $this->getFields();
        $dataList = $this->getDataList();

        $fileName = '用户收益记录' . date('YmdHis');
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
