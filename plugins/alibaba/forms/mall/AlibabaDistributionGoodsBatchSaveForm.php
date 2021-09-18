<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;

class AlibabaDistributionGoodsBatchSaveForm extends BaseModel{

    public $goods_list;

    public function rules(){
        return [
            [['goods_list'], 'required']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            foreach($this->goods_list as $item){
                $goods = AlibabaDistributionGoodsList::findOne($item['id']);
                if(!$goods) continue;

                if(empty($item['ali_category_id'])){
                    throw new \Exception("类别不能为空");
                }

                $goods->price           = $item['price'];
                $goods->origin_price    = $item['origin_price'];
                $goods->ali_category_id = implode(",", $item['ali_category_id']);
                $goods->updated_at      = time();

                if(!$goods->save()){
                    throw new \Exception($this->responseErrorMsg($goods));
                }
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => "保存成功"
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}