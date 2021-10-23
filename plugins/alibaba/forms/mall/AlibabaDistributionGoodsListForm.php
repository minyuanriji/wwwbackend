<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\forms\mall\export\distributionGoodsListExport;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaApp;
use app\plugins\alibaba\models\AlibabaDistributionGoodsCategory;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionGoodsSku;

class AlibabaDistributionGoodsListForm extends BaseModel{

    public $page;
    public $app_id;
    public $keyword;
    public $keyword_1;
    public $sort_prop;
    public $sort_type;
    public $flag;
    public $fields;

    public function rules(){
        return [
            [['page', 'app_id', 'keyword_1'], 'integer'],
            [['keyword', 'sort_type', 'sort_prop'], 'string'],
            [['flag'], 'trim'],
            [['fields'], 'safe'],
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = AlibabaDistributionGoodsList::find()->alias("g");
            $query->innerJoin(["a" => AlibabaApp::tableName()], "a.id=g.app_id");

            $query->where([
                "g.is_delete" => 0,
                "a.is_delete" => 0,
                "a.type"      => "distribution"
            ]);

            if($this->app_id){
                $query->andWhere(["g.app_id" => $this->app_id]);
            }

            //关键词搜索
            $query->keyword($this->keyword_1 == 1, ['g.ali_offerId' => $this->keyword]);
            $query->keyword($this->keyword_1 == 2, ['like', 'g.name', $this->keyword]);

            if ($this->sort_prop && $this->sort_type) {
                $orderBy = 'g.' . $this->sort_prop . ($this->sort_type ? ' ' . $this->sort_type : ' DESC');
            } else {
                $orderBy = "g.id DESC";
            }
            $query->orderBy($orderBy);

            if ($this->flag == "EXPORT") {
                $new_query = clone $query;
                $exp = new distributionGoodsListExport();
                $exp->fieldsKeyList = $this->fields;
                $exp->export($new_query, 'g.');
                return false;
            }

            $list = $query->asArray()->page($pagination, 20, $this->page)->all();
            if($list){
                foreach($list as &$item){
                    $item['free_edit'] = 0;
                    $skuList = AlibabaDistributionGoodsSku::find()->where([
                        "goods_id"  => $item['id'],
                        "is_delete" => 0
                    ])->asArray()->all();
                    $item['sku_infos'] = @json_decode($item['sku_infos'], true);
                    $skuValues = isset($item['sku_infos']['values']) ? $item['sku_infos']['values'] : [];
                    if($skuList){
                        foreach($skuList as &$skuItem){
                            $attributes = explode(",", $skuItem['ali_attributes']);
                            $skuItem['ali_attributes_label'] = [];
                            foreach($attributes as $value){
                                if(isset($skuValues[$value])){
                                    $skuItem['ali_attributes_label'][] = $skuValues[$value];
                                }
                            }
                            if(empty($skuItem['name'])){
                                $skuItem['name'] = implode("，", $skuItem['ali_attributes_label']);
                            }
                            $skuItem['free_edit'] = 0;
                            $skuItem['ali_attributes_label'] = implode("，", $skuItem['ali_attributes_label']);
                        }

                    }
                    $item['sku_list']        = $skuList;
                    $item['ali_category_id'] = explode(",", $item['ali_category_id']);
                    $item['ali_data_json']   = @json_decode($item['ali_data_json'], true);
                    $item['categorys']       = [];

                    if($item['ali_category_id']){
                        $item['categorys'] = AlibabaDistributionGoodsCategory::find()->andWhere([
                            "AND",
                            ["IN", "ali_cat_id", $item['ali_category_id']],
                            ["is_delete" => 0]
                        ])->select(["name"])->asArray()->all();
                    }

                    $item['ali_product_info'] = (array)@json_decode($item['ali_product_info'], true);
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list ?: [],
                    'export_list' => (new distributionGoodsListExport())->fieldsList(),
                    'pagination' => $pagination
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }
}