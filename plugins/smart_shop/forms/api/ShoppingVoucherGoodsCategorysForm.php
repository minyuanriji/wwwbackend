<?php

namespace app\plugins\smart_shop\forms\api;

use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsCategory;

class ShoppingVoucherGoodsCategorysForm extends BaseModel implements ICacheForm {

    public $mall_id;
    public $parent_id = 0;

    public function rules(){
        return [
            [['parent_id', 'mall_id'], 'integer']
        ];
    }

    /**
     * @return APICacheDataForm
     */
    public function getSourceDataForm(){
        try {
            $rows = AlibabaDistributionGoodsCategory::find()->where([
                "mall_id"       => $this->mall_id,
                "ali_parent_id" => (int)$this->parent_id,
                "is_delete"     => 0
            ])->select(["ali_cat_id", "ali_parent_id", "name", "cover_url"])
              ->orderBy("sort DESC")->asArray()->all();

            return new APICacheDataForm([
                "sourceData" => [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => $rows ? $rows : []
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
        return [(int)$this->parent_id];
    }
}