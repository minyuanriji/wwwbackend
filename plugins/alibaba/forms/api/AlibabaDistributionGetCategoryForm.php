<?php

namespace app\plugins\alibaba\forms\api;

use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsCategory;

class AlibabaDistributionGetCategoryForm extends BaseModel implements ICacheForm {

    public $mall_id;
    public $host_info;

    /**
     * @return APICacheDataForm
     */
    public function getSourceDataForm(){
        try {
            $datas = [];
            $rows = AlibabaDistributionGoodsCategory::find()->where([
                "is_delete"     => 0,
                "mall_id"       => $this->mall_id,
                "ali_parent_id" => 0
            ])->orderBy("sort DESC")->asArray()->all();
            foreach($rows as $row){
                $data = ["ali_cat_id" => $row['ali_cat_id'], "name" => $row['name'], "cover_url" => $row['cover_url'], 'children' => []];
                $children = AlibabaDistributionGoodsCategory::find()->where([
                    "is_delete"     => 0,
                    "mall_id"       => $this->mall_id,
                    "ali_parent_id" => $row['ali_cat_id']
                ])->orderBy("sort DESC")->asArray()->all();
                if($children){
                    foreach($children as $child){
                        $data['children'][] = ["ali_cat_id" => $child['ali_cat_id'], "name" => $child['name'], "cover_url" => $child['cover_url']];
                    }
                }
                $datas[] = $data;
            }

            return new APICacheDataForm([
                "sourceData" => [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => $datas
                ]
            ]);
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * @return array
     */
    public function getCacheKey(){
        return ['host_info'];
    }
}