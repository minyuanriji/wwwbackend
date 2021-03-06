<?php
/**
  * @link:http://www.gdqijianshi.com/
 * copyright: Copyright (c) 2020 广东七件事集团
 * author: jack_guo
 */

namespace app\forms\mall\export;

use app\core\CsvExport;

class StepStatisticsExport extends BaseExport
{

    public function fieldsList()
    {
        return [
            [
                'key' => 'begin_at',
                'value' => '活动时间',
            ],
            [
                'key' => 'title',
                'value' => '活动名称',
            ],
            [
                'key' => 'step_num',
                'value' => '挑战步数',
            ],
            [
                'key' => 'participate_num',
                'value' => '报名人数',
            ],
            [
                'key' => 'success_num',
                'value' => '挑战成功人数',
            ],
            [
                'key' => 'currency',
                'value' => '奖金池总额',
            ],
            [
                'key' => 'put_currency',
                'value' => '报名活力币消耗',
            ],
        ];
    }

    public function export($query)
    {
        $list = $query
            ->asArray()
            ->all();
        $this->transform($list);
        $this->getFields();
        $dataList = $this->getDataList();

        $fileName = '步数挑战统计' . date('YmdHis');
        (new CsvExport())->export($dataList, $this->fieldsNameList, $fileName);
    }

    protected function transform($list)
    {
        $newList = [];
        $arr = [];

        $number = 1;
        foreach ($list as $key => $item) {
            $arr['number'] = $number++;
            $arr['success_100'] = (($item['all_user'] == 0) ? 0 : (bcdiv($item['success_user'], $item['all_user'], 4) * 100)) . '%';
            $item['step_num'] = intval($item['step_num']);
            $item['participate_num'] = intval($item['participate_num']);
            $item['success_num'] = intval($item['success_num']);
            $item['currency'] = floatval($item['currency']);
            $item['put_currency'] = intval($item['put_currency']);

            $arr = array_merge($arr, $item);

            $newList[] = $arr;
        }
        $this->dataList = $newList;
    }

    protected function getFields()
    {
        $arr = [];
        foreach ($this->fieldsList() as $key => $item) {
            $arr[$key] = $item['key'];
        }
        $this->fieldsKeyList = $arr;
        parent::getFields(); // TODO: Change the autogenerated stub
    }
}
