<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-19
 * Time: 16:59
 */

namespace app\plugins\area\forms\mall;

use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\models\CommonOrder;
use app\models\CommonOrderDetail;
use app\models\Goods;
use app\models\GoodsAttr;
use app\plugins\area\forms\common\AreaLevelCommon;
use app\plugins\area\models\AreaGoods;
use app\plugins\area\models\AreaGoodsDetail;
use app\plugins\area\models\AreaLevel;
use app\plugins\area\models\AreaSetting;


class AreaGoodsForm extends BaseModel
{
    public $goods_id;
    public $goods_type = CommonOrderDetail::TYPE_MALL_GOODS;
    public $price_type;
    public $is_alone;
    public $province_price;
    public $district_price;
    public $town_price;
    public $city_price;

    public function rules()
    {
        return [

            [['goods_id', 'price_type', 'is_alone', 'goods_type'], 'integer'],
            [['province_price', 'district_price', 'town_price', 'city_price'], 'number'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'price_type' => '分红佣金类型',
            'is_alone' => '是否独立设置',
            'goods_id' => '经销商等级',
            'goods_type' => '商品类型',
            'province_price' => 'Province Price',
            'district_price' => 'District Price',
            'town_price' => 'Town Price',
            'city_price' => 'City Price',
        ];
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-20
     * @Time: 17:30
     * @Note: 设置商品经销的公共函数
     */


    public function saveAreaGoodsSetting()
    {
        if (!$this->is_alone) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        }
        $area_goods = AreaGoods::findOne(['goods_id' => $this->goods_id, 'is_delete' => 0, 'goods_type' => $this->goods_type]);
        if (!$area_goods) {
            $area_goods = new AreaGoods();
            $area_goods->goods_id = $this->goods_id;
            $area_goods->goods_type;
            $area_goods->mall_id = \Yii::$app->mall->id;
        }
        $area_goods->price_type=$this->price_type;
        $area_goods->is_alone = $this->is_alone;
        if (!$area_goods->save()) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '保存失败',
                'error' => $area_goods->getErrors()
            ];
        }
        $area_goods_detail = AreaGoodsDetail::findOne(['is_delete' => 0, 'area_goods_id' => $area_goods->id]);
        if (!$area_goods_detail) {
            $area_goods_detail = new AreaGoodsDetail();
            $area_goods_detail->mall_id = \Yii::$app->mall->id;
            $area_goods_detail->goods_id = $this->goods_id;
        }
        $area_goods_detail->area_goods_id = $area_goods->id;
        $area_goods_detail->province_price = $this->province_price;
        $area_goods_detail->district_price = $this->district_price;
        $area_goods_detail->city_price = $this->city_price;
        $area_goods_detail->town_price = $this->town_price;
       if(!$area_goods_detail->save()){

           return ['code' => ApiCode::CODE_FAIL, 'msg' => '保存失败' , 'error' => $area_goods_detail->getErrors()];
       }
        return ['code' => ApiCode::CODE_SUCCESS, 'msg' => '保存成功'];
    }


    public function getAreaGoodsSetting()
    {
        $is_enable = AreaSetting::getValueByKey(AreaSetting::IS_ENABLE);
        if (!$is_enable) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '未启区域代理插件',
            ];
        }
        $setting['goods_id'] = $this->goods_id;
        $area_goods = AreaGoods::findOne(['is_delete' => 0, 'goods_id' => $this->goods_id, 'goods_type' => CommonOrderDetail::TYPE_MALL_GOODS]);
        $setting['province_price'] = 0;
        $setting['city_price'] = 0;
        $setting['district_price'] = 0;
        $setting['town_price'] = 0;
        if (!$area_goods) {
            $setting['is_alone'] = 0;
            $setting['price_type'] = 0;
        } else {
            $setting['is_alone'] = $area_goods->is_alone;
            $setting['price_type'] = $area_goods->price_type;
            $detail = AreaGoodsDetail::findOne(['area_goods_id' => $area_goods->id, 'is_delete' => 0]);

            if ($detail) {
                $setting['province_price'] = $detail->province_price;
                $setting['city_price'] = $detail->city_price;
                $setting['district_price'] = $detail->district_price;
                $setting['town_price'] = $detail->town_price;
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'setting' => $setting,
            ]
        ];
    }
}