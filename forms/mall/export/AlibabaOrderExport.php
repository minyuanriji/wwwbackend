<?php

namespace app\forms\mall\export;

use app\core\CsvExport;
use app\helpers\SerializeHelper;

class AlibabaOrderExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'order_id',
                'value' => '订单ID',
            ],
            [
                'key' => 'order_no',
                'value' => '订单号',
            ],
            [
                'key' => 'goods_id',
                'value' => '商品ID',
            ],
            [
                'key' => 'goods_name',
                'value' => '商品名称',
            ],
            [
                'key' => 'goods_sku',
                'value' => '规格',
            ],
            [
                'key' => 'buy_sum',
                'value' => '购买数量',
            ],
            [
                'key' => 'nickname',
                'value' => '支付用户昵称',
            ],
            [
                'key' => 'user_id',
                'value' => '用户ID',
            ],
            [
                'key' => 'pay_status',
                'value' => '支付状态',
            ],
            [
                'key' => 'order_status',
                'value' => '订单状态',
            ],
            [
                'key' => 'shopping_voucher',
                'value' => '消费红包',
            ],
            [
                'key' => 'freight',
                'value' => '运费',
            ],
            [
                'key' => 'total_money',
                'value' => '总计',
            ],
            [
                'key' => 'pay_time',
                'value' => '支付时间',
            ],
            [
                'key' => 'receiving_people',
                'value' => '收件人',
            ],
            [
                'key' => 'mobile',
                'value' => '手机号',
            ],
            [
                'key' => 'address',
                'value' => '收件人地址',
            ],
            [
                'key' => 'remarks',
                'value' => '备注',
            ],
        ];
    }

    public function export($query, $alias = '')
    {
        $orderBy = $alias . 'id DESC';
        $list = $query->orderBy($orderBy)->asArray()->all();
        if ($list) {
            foreach ($list as &$item) {
                $item['sku_labels'] = json_decode($item['sku_labels'], true);
                $item['total_shopping_voucher_price'] = $item['shopping_voucher_decode_price'] + $item['shopping_voucher_express_use_num'];
            }
        }
        $this->transform($list);
        $this->getFields();
        $dataList = $this->getDataList();

        $fileName = '1688订单--' . date('YmdHis');
        (new CsvExport())->export($dataList, $this->fieldsNameList, $fileName);
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 1;
        $goods_sku = [];
        $pay_status = '';
        $order_status = '';
        foreach ($list as $key => $item) {
            $arr = [];
            if ($item['sku_labels']) {
                foreach ($item['sku_labels'] as $sku) {
                    $goods_sku[$key] .= $sku . PHP_EOL;
                }
            }
            if ($item['refund_status'] == 'finished' && $item['is_refund'] == 1) {
                $pay_status = '已退款';
            } else if ($item['refund_status'] == 'apply') {
                $pay_status = '退款中';
            } else if ($item['refund_status'] == 'agree') {
                $pay_status = '同意退款';
            } else if ($item['refund_status'] == 'refused') {
                $pay_status = '拒绝退款';
            } else if ($item['is_pay'] == 1 && $item['is_refund'] == 0 && $item['refund_status'] == 'none') {
                $pay_status = '已支付';
            } else if ($item['is_pay'] == 0) {
                $pay_status = '未支付';
            }
            if ($item['status'] == 'unpaid') {
                $order_status = '未支付';
            } else if ($item['status'] == 'paid') {
                $order_status = '已支付';
            } else if ($item['status'] == 'invalid') {
                $order_status = '无效';
            }
            $arr['number'] = $number++;
            $arr['order_id'] = $item['id'];
            $arr['order_no'] = $item['order_no'];
            $arr['goods_id'] = $item['goods_id'];
            $arr['goods_name'] = $item['goods_name'];
            $arr['goods_sku'] = $goods_sku[$key];
            $arr['buy_sum'] = $item['num'];
            $arr['nickname'] = $item['nickname'];
            $arr['user_id'] = $item['user_id'];
            $arr['pay_status'] = $pay_status;
            $arr['order_status'] = $order_status;
            $arr['shopping_voucher'] = $item['shopping_voucher_decode_price'] ?? 0;
            $arr['freight'] = $item['shopping_voucher_express_use_num'];
            $arr['total_money'] = $item['total_shopping_voucher_price'];
            $arr['pay_time'] = $this->getDateTime($item['pay_at']);
            $arr['receiving_people'] = $item['name'];
            $arr['mobile'] = $item['mobile'];
            $arr['address'] = $item['address'];
            $arr['remarks'] = $item['do_error'];
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }
}
