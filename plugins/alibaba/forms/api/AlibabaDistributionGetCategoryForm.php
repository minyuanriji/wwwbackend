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
                "is_delete" => 0,
                "mall_id" => $this->mall_id
            ])->orderBy("sort DESC")->asArray()->all();
            while($rows){
                $row = array_shift($rows);
                $data = ["ali_cat_id" => $row['ali_cat_id'], "name" => $row['name'], "cover_url" => $row['cover_url'], 'children' => []];
                if(empty($data['cover_url'])){
                    $data['cover_url'] =  $this->host_info . "/web/statics/img/mall/default_img.png";
                }
                if(!$row['ali_parent_id']){
                    $datas[$row["ali_cat_id"]] = $data;
                }else{
                    $datas[$row['ali_parent_id']]['children'][] = $data;
                }
            }

            $_datas = [];
            foreach($datas as $item){
                if(!isset($item['ali_cat_id']))
                    continue;
                $_datas[] = $item;
            }
            $datas = $_datas;
            unset($_datas);

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