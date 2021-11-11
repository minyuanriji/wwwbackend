<?php

namespace app\forms\mall\export;

use app\core\CsvExport;
use app\helpers\SerializeHelper;
use app\models\User;
use app\plugins\boss\models\BossLevel;

class BossExport extends BaseExport
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
                'key' => 'role_type',
                'value' => '身份',
            ],
            [
                'key' => 'accumulated_commission',
                'value' => '累计佣金',
            ],
            [
                'key' => 'shareholde_level',
                'value' => '股东等级',
            ],
            [
                'key' => 'created_at',
                'value' => '时间',
            ],
        ];
    }

    public function export($query, $alias = '')
    {
        $orderBy = $alias . 'created_at';
        $list = $query->orderBy($orderBy)->asArray()->all();
        if ($list) {
            foreach ($list as &$item){
                $item['extra'] = $item['extra'] ? SerializeHelper::decode($item['extra']) : [];
                $item['content'] = $item['content'] ? SerializeHelper::decode($item['content']):[];
            }
        }
        $this->transform($list);
        $this->getFields();
        $dataList = $this->getDataList();

        $fileName = '股东列表' . date('YmdHis');
        (new CsvExport())->export($dataList, $this->fieldsNameList, $fileName);
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 1;
        foreach ($list as $item) {
            $arr = [];
            $arr['number'] = $number++;
            $arr['user_id'] = $item['user'][0]['id'];
            $arr['nickname'] = $item['user'][0]['nickname'];
            $arr['mobile'] = $item['user'][0]['mobile'];
            $arr['role_type'] = (new User())::getRoleType($item['user'][0]['role_type']);
            $arr['accumulated_commission'] = $item['total_price'];
            $bossLevel = BossLevel::find()->where([
                'id' => $item['level_id'],
                'is_delete' => 0
            ])->one();
            if (!$bossLevel) {
                $arr['shareholde_level'] = '';
            } else {
                $arr['shareholde_level'] = $bossLevel->name;
            }
            $arr['created_at'] = $this->getDateTime($item['created_at']);
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }
}
