<?php

namespace app\plugins\alibaba\forms\api;

use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;

class AlibabaDistributionSearchGoodsForm extends BaseModel implements ICacheForm {

    public $mall_id;
    public $user_id;
    public $page;
    public $ali_cat_id;

    public function rules(){
        return [
            [['page'], 'required'],
            [['mall_id', 'user_id', 'page', 'ali_cat_id'], 'integer']
        ];
    }

    /**
     * @return APICacheDataForm
     */
    public function getSourceDataForm(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $query = AlibabaDistributionGoodsList::find()->where(["is_delete" => 0]);

            if($this->ali_cat_id){
                $query->andWhere("FIND_IN_SET('{$this->ali_cat_id}', ali_category_id)");
            }

            $orderBy = "id DESC";
            $query->orderBy($orderBy);


            $selects = ["id", "name", "cover_url", "price", "origin_price"];
            $list = $query->asArray()->select($selects)->page($pagination, 20, $this->page)->all();
            if($list){
                foreach($list as &$item){

                }
            }

            return new APICacheDataForm([
                "sourceData" => [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'list' => $list ? $list : [],
                        'pagination' => $pagination
                    ]
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
        $keys = [$this->mall_id, $this->user_id];
        return $keys;
    }
}