<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaApp;
use app\plugins\alibaba\models\AlibabaDistributionGoodsCategory;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionGoodsSku;
use lin010\alibaba\c2b2b\api\GetAddress;
use lin010\alibaba\c2b2b\api\GetCategoryInfo;
use lin010\alibaba\c2b2b\api\GetCategoryInfoResponse;
use lin010\alibaba\c2b2b\api\GetGoodsDetail;
use lin010\alibaba\c2b2b\api\GetGoodsDetailResponse;
use lin010\alibaba\c2b2b\api\OrderGetPreview;
use lin010\alibaba\c2b2b\api\OrderGetPreviewResponse;
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

                $goods->name              = $info['title'];
                $goods->cover_url         = $info['imgUrl'];
                $goods->updated_at        = time();
                $goods->ali_data_json     = json_encode($info);
                $goods->price_rate        = 1000;
                $goods->origin_price_rate = 1000;
                $goods->price             = ($goods->price_rate/100) * $info['currentPrice'];
                $goods->origin_price      = ($goods->origin_price_rate/100) * $info['currentPrice'];
                $goods->ali_category_id   = 0;
                $goods->is_delete         = 0;
                $goods->ali_product_info = json_encode([
                    "biz" => $res->bizGroupInfos,
                    "info" => $res->productInfo
                ]);

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

                if(!$goods->save()){
                    throw new \Exception($this->responseErrorMsg($goods));
                }

                //获取类目信息
                $goods->ali_category_id = $this->getCategoryIds($distribution, $res->productInfo['categoryID']);

                //计算1688运费
                if(isset($res->productInfo['skuInfos']) && !empty($res->productInfo['skuInfos'])){
                    $goods->ali_freight_price = $this->getAliFreightPrice($distribution, $app->access_token, [
                        'offerId'   => $goods->ali_offerId,
                        'specId'    => $res->productInfo['skuInfos'][0]['specId'],
                        'quantity'  => 1
                    ]);
                }else{
                    $goods->ali_freight_price = $this->getAliFreightPrice($distribution, $app->access_token, [
                        'offerId'   => $goods->ali_offerId,
                        'quantity'  => 1
                    ]);
                }
                $goods->freight_price_rate = 100;
                $goods->freight_price = ($goods->freight_price_rate/100) * $goods->ali_freight_price;

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
                    $sku->price          = ($goods->price_rate/100) * $skuInfo['consignPrice'];
                    $sku->origin_price   = ($goods->origin_price_rate/100) * $skuInfo['consignPrice'];
                    $sku->cargo_number   = $skuInfo['cargoNumber'];
                    $sku->amount_on_sale = $skuInfo['amountOnSale'];
                    $sku->ali_price      = $skuInfo['consignPrice'];
                    $sku->freight_price  = $goods->freight_price;
                    $sku->consign_price  = $skuInfo['consignPrice'];
                    $sku->updated_at     = time();
                    $sku->is_delete      = 0;
                    $sku->ali_attributes = implode(",", $attributes);
                    $sku->ali_num        = 1;
                    $sku->name           = implode("，", $labels);

                    if(!$sku->save()){
                        throw new \Exception($this->responseErrorMsg($sku));
                    }

                    $skuAttr = $sku->getAttributes();
                    $skuAttr['ali_attributes_label'] = implode("，", $labels);
                    $skuAttr['free_edit'] = 0;
                    $skuList[] = $skuAttr;


                }


                $goods->sku_infos = json_encode($skuInfos);
                if(!$goods->save()){
                    throw new \Exception($this->responseErrorMsg($goods));
                }

                $insertData = $goods->getAttributes();
                $insertData['ali_data_json']   = $info;
                $insertData['sku_list']        = $skuList;
                $insertData['free_edit']       = 0;
                $insertData['ali_category_id'] = explode(",", $goods->ali_category_id);

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
                'msg'  => $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    /**
     * 获取1688的运费
     * @param Distribution $distribution
     * @param $token
     * @param $cargoParamList
     * @return float
     * @throws \Exception
     */
    private function getAliFreightPrice(Distribution $distribution, $token, $cargoParamList){
        //解析1688的地址
        $res = $distribution->requestWithToken(new GetAddress([
            "addressInfo" => "广东省 广州市 白云区鹤龙一路"
        ]), $token);
        if(!empty($res->error)){
            throw new \Exception($res->error);
        }
        $res = $distribution->requestWithToken(new OrderGetPreview([
            "addressParam" => json_encode([
                "fullName"     => "补商汇",
                "mobile"       => "",
                "phone"        => "",
                "postCode"     => isset($aliAddrInfo['postCode']) ? $aliAddrInfo['postCode'] : "",
                "cityText"     => "广州市",
                "provinceText" => "广东省",
                "areaText"     => "白云区",
                "address"      => "鹤龙一路",
                "districtCode" => isset($aliAddrInfo['addressCode']) ? $aliAddrInfo['addressCode'] : ""
            ]),
            "cargoParamList" => json_encode($cargoParamList)
        ]), $token);
        if(!empty($res->error)){
            throw new \Exception($res->error);
        }
        if(!$res instanceof OrderGetPreviewResponse){
            throw new \Exception("[OrderGetPreviewResponse]返回结果异常");
        }

        $freightPriceFen = 0;
        foreach($res->result as $item){
            $freightPriceFen += $item['sumCarriage'];
        }

        return round($freightPriceFen/100, 2);
    }

    /**
     * 获取类别
     * @param Distribution $distribution
     * @param $categoryID
     * @return string
     * @throws \Exception
     */
    private function getCategoryIds(Distribution $distribution, $categoryID){
        $idArray = [];
        $category = AlibabaDistributionGoodsCategory::findOne([
            "ali_cat_id" => $categoryID
        ]);
        if(!$category){
            $res = $distribution->request(new GetCategoryInfo([
                "categoryID" => $categoryID
            ]));
            if(!$res instanceof GetCategoryInfoResponse){
                throw new \Exception("[GetCategoryInfoResponse]返回结果异常");
            }
            if($res->error){
                throw new \Exception($res->error);
            }
            $parentId = $res->getParentID();
            if($parentId){
                $category = AlibabaDistributionGoodsCategory::findOne([
                    "ali_cat_id" => $parentId
                ]);
            }
        }
        if($category){
            $idArray[] = $category->ali_cat_id;
            while($category->ali_parent_id){
                $category = AlibabaDistributionGoodsCategory::findOne([
                    "ali_cat_id" => $category->ali_parent_id
                ]);
                if(!$category) break;
                $idArray[] = $category->ali_cat_id;
            }
            $idArray = array_reverse($idArray);
        }
        return implode(",", array_slice($idArray, 0, 2));
    }
}