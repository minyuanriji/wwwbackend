<?php

namespace app\forms\mall\export;

use app\core\CsvExport;
use app\plugins\alibaba\models\AlibabaDistributionGoodsCategory;
use app\plugins\alibaba\models\AlibabaDistributionGoodsSku;

class distributionGoodsListExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'id',
                'value' => 'ID',
            ],
            [
                'key' => 'categorys',
                'value' => '类目',
            ],
            [
                'key' => 'name',
                'value' => '标题',
            ],
            [
                'key' => 'ali_offerId',
                'value' => '编号',
            ],
            [
                'key' => 'freight_price',
                'value' => '运费（元）',
            ],
            [
                'key' => 'ali_freight_price',
                'value' => '1688运费',
            ],
            [
                'key' => 'price_rate',
                'value' => '零售比（%）',
            ],
            [
                'key' => 'price',
                'value' => '零售价',
            ],
            [
                'key' => 'currentPrice',
                'value' => '分销价',
            ],
            [
                'key' => 'is_recommend',
                'value' => '是否每日推荐',
            ],
            [
                'key' => 'created_at',
                'value' => '添加时间',
            ],
        ];
    }

    public function export($query, $alias = '')
    {
        $orderBy = $alias . 'created_at';
        $list = $query->orderBy($orderBy)->asArray()->all();
        if ($list) {
            foreach($list as &$item){
                $item['free_edit'] = 0;
                $item['ali_category_id'] = explode(",", $item['ali_category_id']);
                $item['ali_data_json']   = @json_decode($item['ali_data_json'], true);
                $item['categorys']       = [];
                if($item['ali_category_id']){
                    $item['categorys'] = AlibabaDistributionGoodsCategory::find()->andWhere([
                        "AND",
                        ["IN", "ali_cat_id", $item['ali_category_id']],
                        ["is_delete" => 0]
                    ])->select(["name"])->asArray()->all();
                    $item['goods_categorys'] = '';
                    if ($item['categorys']) {
                        foreach ($item['categorys'] as $categorys) {
                            $item['goods_categorys'] .= $categorys['name'] . '  ';
                        }
                    }
                }
            }
        }
        $this->transform($list);
        $this->getFields();
        $dataList = $this->getDataList();

        $fileName = '1688商品列表' . date('YmdHis');
        (new CsvExport())->export($dataList, $this->fieldsNameList, $fileName);
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 1;
        foreach ($list as $item) {
            $arr = [];
            $arr['number'] = $number++;
            $arr['id'] = $item['id'];
            $arr['categorys'] = $item['goods_categorys'];
            $arr['name'] = $item['name'];
            $arr['ali_offerId'] = $item['ali_offerId'];
            $arr['freight_price'] = $item['freight_price'];
            $arr['ali_freight_price'] = $item['ali_freight_price'];
            $arr['price_rate'] = $item['price_rate'];
            $arr['price'] = $item['price'];
            $arr['currentPrice'] = $item['ali_data_json']['currentPrice'];
            $arr['is_recommend'] = $item['is_recommend'] == 1 ? '是' : '否';
            $arr['created_at'] = $this->getDateTime($item['created_at']);
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }
}
