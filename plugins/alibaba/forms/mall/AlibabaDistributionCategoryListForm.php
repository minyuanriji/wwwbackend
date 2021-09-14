<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsCategory;

class AlibabaDistributionCategoryListForm extends BaseModel{

    public $page;
    public $parent_id;

    public function rules(){
        return [
            [['page', 'parent_id'], 'integer']
        ];
    }

    public function getList(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = AlibabaDistributionGoodsCategory::find()->where(["is_delete" => 0]);
            $query->andWhere(["ali_parent_id" => ($this->parent_id ? $this->parent_id : 0)]);

            $orderBy = "sort DESC,id DESC";
            $query->orderBy($orderBy);

            $list = $query->asArray()->page($pagination, 20, $this->page)->all();
            if($list){
                foreach($list as &$item){

                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => ($list ? $list : []),
                    'pagination' => $pagination,
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