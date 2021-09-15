<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;

class AlibabaDistributionAliGoodsImportForm extends BaseModel{

    public $goods_array;
    public $app_id;

    public function rules(){
        return [
            [['app_id', 'goods_array'], 'required']
        ];
    }

    public function import(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $insertDatas = [];
            foreach($this->goods_array as $info){
                $uniqueData = [
                    "mall_id"     => \Yii::$app->mall->id,
                    "app_id"      => $this->app_id,
                    "ali_offerId" => $info['offerId']
                ];
                $goods = AlibabaDistributionGoodsList::findOne($uniqueData);
                if(!$goods){
                    $goods = new AlibabaDistributionGoodsList(array_merge($uniqueData, [
                        "created_at"  => time(),
                    ]));
                }
                $goods->name            = $info['title'];
                $goods->cover_url       = $info['imgUrl'];
                $goods->updated_at      = time();
                $goods->ali_data_json   = json_encode($info);
                $goods->price           = $info['currentPrice'];
                $goods->ali_category_id = 0;
                $goods->is_delete       = 0;
                if(!$goods->save()){
                    throw new \Exception($this->responseErrorMsg($goods));
                }

                $insertData = $goods->getAttributes();
                $insertData['ali_data_json'] = $info;
                $insertDatas[] = $insertData;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $insertDatas
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}