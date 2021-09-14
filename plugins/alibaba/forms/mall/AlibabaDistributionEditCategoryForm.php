<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsCategory;

class AlibabaDistributionEditCategoryForm extends BaseModel{

    public $id;
    public $name;
    public $cover_url;
    public $sort;

    public function rules(){
        return [
            [['id', 'name', 'cover_url'], 'required'],
            [['sort'], 'safe']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $category = AlibabaDistributionGoodsCategory::findOne($this->id);
            if(!$category){
                throw new \Exception("类别不存在");
            }

            $category->sort       = $this->sort;
            $category->name       = $this->name;
            $category->cover_url  = $this->cover_url;
            $category->updated_at = time();
            if(!$category->save()){
                throw new \Exception($this->responseErrorMsg($category));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '保存成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}