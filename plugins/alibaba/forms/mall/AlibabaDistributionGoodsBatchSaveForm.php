<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaApp;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use lin010\alibaba\c2b2b\api\GetGoodsDetail;
use lin010\alibaba\c2b2b\api\GetGoodsDetailResponse;
use lin010\alibaba\c2b2b\Distribution;

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

                //如果详情为空，通过1688接口获取商品详情
                if(empty($goods->ali_product_info)){
                    $app = AlibabaApp::findOne($goods->app_id);

                    $distribution = new Distribution($app->app_key, $app->secret);
                    $res = $distribution->requestWithToken(new GetGoodsDetail([
                        "offerId" => $goods->ali_offerId
                    ]), $app->access_token);
                    if(!$res instanceof GetGoodsDetailResponse){
                        throw new \Exception("[GetGoodsDetailResponse]返回结果异常");
                    }
                    if($res->error){
                        throw new \Exception($res->error);
                    }
                    $goods->ali_product_info = json_encode([
                        "biz" => $res->bizGroupInfos,
                        "info" => $res->productInfo
                    ]);
                }


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