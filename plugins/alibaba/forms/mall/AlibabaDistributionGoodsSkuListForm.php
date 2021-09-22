<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaApp;
use app\plugins\alibaba\models\AlibabaDistributionGoodsCategory;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionGoodsSku;

class AlibabaDistributionGoodsSkuListForm extends BaseModel{

    public $page;
    public $app_id;

    public function rules(){
        return [
            [['page', 'app_id'], 'integer']
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = AlibabaDistributionGoodsSku::find()->alias("k");
            $query->innerJoin(["g" => AlibabaDistributionGoodsList::tableName()], "g.id=k.goods_id");
            $query->innerJoin(["a" => AlibabaApp::tableName()], "a.id=g.app_id");

            $query->where([
                "k.is_delete" => 0,
                "g.is_delete" => 0,
                "a.is_delete" => 0,
                "a.type"      => "distribution"
            ]);

            if($this->app_id){
                $query->andWhere(["g.app_id" => $this->app_id]);
            }

            $orderBy = "g.id DESC";
            $query->orderBy($orderBy);

            $selects = ["k.id", "k.ali_attributes", "k.price", "k.goods_id", "g.name", "g.cover_url", "g.sku_infos"];
            $list = $query->select($selects)->asArray()->page($pagination, 20, $this->page)->all();
            if($list){
                foreach($list as &$item){
                    $item['sku_infos'] = @json_decode($item['sku_infos'], true);
                    $attrs = explode(",", $item['ali_attributes']);
                    $labels = [];
                    foreach($attrs as $attr){
                        if(!empty($attr) && isset($item['sku_infos']['values'][$attr])){
                            $part = explode(":", $attr);
                            $attrId = isset($part[0]) ? $part[0] : 0;
                            if(isset($item['sku_infos']['group'][$attrId])){
                                $labels[] = $item['sku_infos']['group'][$attrId]['attributeName'] . "：" . $item['sku_infos']['values'][$attr];
                            }
                        }
                    }
                    $item['name'] = $item['name'] . "【".implode("，", $labels)."】";
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list ? $list : [],
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