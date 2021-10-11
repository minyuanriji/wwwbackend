<?php
/*
 * @link:http://www.@copyright: Copyright (c) @Author: Mr.Lin
 * @Email: 746027209@qq.com
 * @Date: 2021-07-06 14:13
 */

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsSku;

class AlibabaDistributionCopySkuForm extends BaseModel
{
    public $ali_attributes;
    public $ali_num;
    public $ali_price;
    public $ali_sku_id;
    public $ali_spec_id;
    public $amount_on_sale;
    public $cargo_number;
    public $consign_price;
    public $freight_price;
    public $goods_id;
    public $mall_id;
    public $name;
    public $origin_price;
    public $price;

    public function rules()
    {
        return [
            [['ali_attributes', 'ali_num', 'ali_price', 'ali_sku_id', 'ali_spec_id', 'amount_on_sale', 'consign_price', 'freight_price', 'goods_id', 'name', 'origin_price', 'price'], 'required'],
            [['cargo_number'], 'safe'],
        ];
    }

    public function copy()
    {

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $sku = new AlibabaDistributionGoodsSku();
            $sku->mall_id = \Yii::$app->mall->id;
            $sku->goods_id = $this->goods_id;
            $sku->ali_sku_id = $this->ali_sku_id;
            $sku->ali_attributes = $this->ali_attributes;
            $sku->ali_spec_id = $this->ali_spec_id;
            $sku->price = $this->price;
            $sku->origin_price = $this->origin_price;
            $sku->freight_price = $this->freight_price;
            $sku->name = $this->name;
            $sku->ali_num = $this->ali_num;
            $sku->cargo_number = $this->cargo_number;
            $sku->amount_on_sale = $this->amount_on_sale;
            $sku->ali_price = $this->ali_price;
            $sku->consign_price = $this->consign_price;
            if (!$sku->save()) {
                throw new \Exception($this->responseErrorMsg($sku));
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '复制成功', $sku);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }

    }
}