<?php

namespace app\forms\mall\export;

use app\core\CsvExport;

class IntegralLogExport extends BaseExport
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
                'key' => 'change_integral',
                'value' => '变动红包',
            ],
            [
                'key' => 'current_integral',
                'value' => '当前红包',
            ],
            [
                'key' => 'desc',
                'value' => '说明',
            ],
            [
                'key' => 'created_at',
                'value' => '充值日期',
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

        $fileName = '用户红包记录' . date('YmdHis');
        (new CsvExport())->export($dataList, $this->fieldsNameList, $fileName);
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 1;
        foreach ($list as $item) {
            $arr = [];
            $arr['number'] = $number++;
            $arr['user_id'] = $item['user_id'];
            $arr['nickname'] = $item['nickname'];
            $arr['mobile'] = $item['mobile'];
            $arr['change_integral'] = $item['integral'];
            $arr['current_integral'] = $item['current_integral'];
            $arr['desc'] = $item['desc'];
            $arr['created_at'] = $this->getDateTime($item['created_at']);
            $newList[] = $arr;
        }
        $this->dataList = $newList;
    }
}
