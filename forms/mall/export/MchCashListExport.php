<?php

namespace app\forms\mall\export;

use app\core\CsvExport;

class MchCashListExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'mch_id',
                'value' => '商家ID',
            ],
            [
                'key' => 'store_name',
                'value' => '店铺名称',
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
        if($list){
            foreach($list as &$item){
                $typeData = @json_decode($item['type_data'], true);
                $item = array_merge($item, is_array($typeData) ? $typeData : []);
                $currentApply += $item['money'];
                if ($item['transfer_status'] == 1) {
                    $currentActual += $item['fact_price'];
                }
            }
        }
        $this->transform($list);
        $this->getFields();
        $dataList = $this->getDataList();

        $fileName = '商家提现记录' . date('YmdHis');
        (new CsvExport())->export($dataList, $this->fieldsNameList, $fileName);
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 1;
        foreach ($list as $item) {
            $arr = [];
            $arr['number'] = $number++;
            $arr['mch_id'] = $item['mch_id'];
            $arr['store_name'] = $item['name'];
            $arr['account_holder'] = $item['bankUserName'];
            $arr['bank_no'] = $item['bankCardNo'];
            $arr['bank_deposit'] = $item['bankName'];
            $arr['apply_money'] = (float)$item['money'];
            $arr['service_charge'] = $item['service_fee_rate'];
            $arr['actual_money'] = $item['fact_price'];
            if ($item['status'] == 0) {
                $arr['type'] = '待处理';
            } elseif ($item['status'] == 1) {
                if ($item['transfer_status'] == 0) {
                    $arr['type'] = '待转账';
                } elseif ($item['transfer_status'] == 1) {
                    $arr['type'] = '已转账';
                } elseif ($item['transfer_status'] == 2) {
                    $arr['type'] = '拒绝转账';
                }
            } elseif ($item['status'] == 2) {
                $arr['type'] = '已驳回';
            }
            $arr['created_at'] = $this->getDateTime($item['created_at']);
            $arr['pay_time'] = $this->getDateTime($item['updated_at']);
            $arr['remarks'] = $item['remark'] . "------" . $item['content'];
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }
}
