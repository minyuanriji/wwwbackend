<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsCategory;

class AlibabaDistributionGetCategoryForm extends BaseModel{

    public function get(){
        try {

            $datas = [];
            $rows = AlibabaDistributionGoodsCategory::find()->where([
                "is_delete" => 0,
                "mall_id" => \Yii::$app->mall->id
            ])->orderBy("ali_parent_id ASC,sort DESC")->asArray()->all();
            while($rows){
                $row = array_shift($rows);
                $data = ["value" => $row['ali_cat_id'], "label" => $row['name'], 'children' => []];
                if(!$row['ali_parent_id']){
                    $datas[$row["ali_cat_id"]] = $data;
                }else{
                    $datas[$row['ali_parent_id']]['children'][] = $data;
                }
            }

            $_datas = [];
            foreach($datas as $item){
                if(!$item['children']){
                    unset($item['children']);
                }else{
                    foreach($item['children'] as &$child){
                        if(!$child['children']){
                            unset($child['children']);
                        }
                    }
                }
                $_datas[] = $item;
            }
            $datas = $_datas;
            unset($_datas);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $datas
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}