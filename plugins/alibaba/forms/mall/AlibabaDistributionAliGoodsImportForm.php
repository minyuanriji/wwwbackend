<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaApp;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionGoodsSku;
use lin010\alibaba\c2b2b\api\GetGoodsDetail;
use lin010\alibaba\c2b2b\api\GetGoodsDetailResponse;
use lin010\alibaba\c2b2b\Distribution;

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

        $t = \Yii::$app->db->beginTransaction();
        try {

            $insertDatas = [];
            foreach($this->goods_array as $info){

                $app = AlibabaApp::findOne($this->app_id);

                $distribution = new Distribution($app->app_key, $app->secret);
                $res = $distribution->requestWithToken(new GetGoodsDetail([
                    "offerId" => $info['offerId']
                ]), $app->access_token);
                if(!$res instanceof GetGoodsDetailResponse){
                    throw new \Exception("[GetGoodsDetailResponse]返回结果异常");
                }
                if($res->error){
                    throw new \Exception($res->error);
                }

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
                $goods->origin_price    = $info['currentPrice'];
                $goods->ali_category_id = 0;
                $goods->is_delete       = 0;
                $goods->ali_product_info = json_encode([
                    "biz" => $res->bizGroupInfos,
                    "info" => $res->productInfo
                ]);
                if(!$goods->save()){
                    throw new \Exception($this->responseErrorMsg($goods));
                }

                //保存规格
                $skuInfos = ['group' => [], 'values' => []];
                foreach($res->productInfo['skuInfos'] as $key => $skuInfo){
                    $attributes = [];
                    foreach($skuInfo['attributes'] as $attr){
                        if(!isset($skuInfos[$attr['attributeID']])){
                            $skuInfos['group'][$attr['attributeID']] = [
                                'attributeID'   => $attr['attributeID'],
                                'skuImageUrl'   => isset($attr['skuImageUrl']) ? $attr['skuImageUrl'] : '',
                                'attributeName' => $attr['attributeName']
                            ];
                        }
                        if(!in_array(trim($attr['attributeValue']), $skuInfos['values'])){
                            $skuInfos['values'][$attr['attributeID'].":".$key] = $attr['attributeValue'];
                        }
                    }
                }

                $skuList = [];
                $valuesMap = array_flip($skuInfos['values']);
                foreach($res->productInfo['skuInfos'] as $skuInfo){
                    $attributes = [];
                    $labels = [];
                    foreach($skuInfo['attributes'] as $attr){
                        $value = trim($attr['attributeValue']);
                        if(isset($valuesMap[$value])){
                            $attributes[] = $valuesMap[$value];
                            $labels[] = $value;
                        }
                    }
                    if(empty($attributes))
                        continue;

                    sort($attributes);

                    $sku = AlibabaDistributionGoodsSku::findOne(["ali_sku_id" => $skuInfo['skuId']]);
                    if(!$sku){
                        $sku = new AlibabaDistributionGoodsSku([
                            "ali_sku_id" => $skuInfo['skuId'],
                            "goods_id"   => $goods->id,
                            "mall_id"    => \Yii::$app->mall->id,
                            "created_at" => time()
                        ]);
                    }
                    $sku->ali_spec_id    = $skuInfo['specId'];
                    $sku->price          = $skuInfo['consignPrice'];
                    $sku->origin_price   = $skuInfo['consignPrice'];
                    $sku->cargo_number   = $skuInfo['cargoNumber'];
                    $sku->amount_on_sale = $skuInfo['amountOnSale'];
                    $sku->ali_price      = $skuInfo['consignPrice'];
                    $sku->consign_price  = $skuInfo['consignPrice'];
                    $sku->updated_at     = time();
                    $sku->is_delete      = 0;
                    $sku->ali_attributes = implode(",", $attributes);

                    if(!$sku->save()){
                        throw new \Exception($this->responseErrorMsg($sku));
                    }

                    $skuAttr = $sku->getAttributes();
                    $skuAttr['ali_attributes_label'] = implode("，", $labels);

                    $skuList[] = $skuAttr;
                }
                $goods->sku_infos = json_encode($skuInfos);
                if(!$goods->save()){
                    throw new \Exception($this->responseErrorMsg($goods));
                }

                $insertData = $goods->getAttributes();
                $insertData['ali_data_json'] = $info;
                $insertData['sku_list']      = $skuList;

                $insertDatas[] = $insertData;
            }

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $insertDatas
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}