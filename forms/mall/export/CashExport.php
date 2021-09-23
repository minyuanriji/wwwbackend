<?php

namespace app\forms\mall\export;

use app\core\CsvExport;
use app\helpers\SerializeHelper;

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
        if ($list) {
            foreach ($list as &$item){
                $item['extra'] = $item['extra'] ? SerializeHelper::decode($item['extra']) : [];
                $item['content'] = $item['content'] ? SerializeHelper::decode($item['content']):[];
            }
        }
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
            $arr['user_id'] = $item['user_id'];
            $arr['nickname'] = $item['nickname'];
            $arr['mobile'] = $item['mobile'];
            $arr['account_holder'] = $item['extra']['name'];
            $arr['bank_no'] = $item['extra']['bank_account'];
            $arr['bank_deposit'] = $item['extra']['bank_name'];
            $arr['apply_money'] = round($item['price'], 2);
            $arr['service_charge'] = round($item['price'] * $item['service_fee_rate'] / 100, 2);
            $arr['actual_money'] = round($item['fact_price'], 2);
            if ($item['status'] == 1) {
                $remarks = $item['content']['validate_content'];
                $type = '待打款';
            } elseif ($item['status'] == 2) {
                $remarks = $item['content']['remittance_content'];
                $type = '已打款';
            } elseif ($item['status'] == 3) {
                $remarks = $item['content']['reject_content'];
                $type = '已驳回';
            } elseif ($item['status'] == 0) {
                $type = '待审核';
            }
            $arr['type'] = $type;
            $arr['created_at'] = $this->getDateTime($item['created_at']);
            $arr['pay_time'] = $this->getDateTime($item['updated_at']);
            $arr['remarks'] = $remarks;
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }
}
