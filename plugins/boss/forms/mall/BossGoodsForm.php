<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-19
 * Time: 16:59
 */

namespace app\plugins\boss\forms\mall;

use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\models\CommonOrder;
use app\models\CommonOrderDetail;
use app\models\Goods;
use app\models\GoodsAttr;
use app\plugins\boss\forms\common\BossLevelCommon;
use app\plugins\boss\models\BossGoods;
use app\plugins\boss\models\BossGoodsDetail;
use app\plugins\boss\models\BossLevel;
use app\plugins\boss\models\BossSetting;


class BossGoodsForm extends BaseModel
{
    public $goods_id;
    public $goods_boss_level_list;
    public $goods_equal_level_list;
    public $goods_over_level_list;
    public $goods_type = CommonOrderDetail::TYPE_MALL_GOODS;
    public $price_type;
    public $equal_price_type;//佣金类型
    public $is_alone;

    public function rules()
    {
        return [

            [['goods_id', 'price_type', 'equal_price_type', 'is_alone', 'goods_type'], 'integer'],
            [['goods_boss_level_list', 'goods_over_level_list', 'goods_equal_level_list'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'price_type' => '团队分红佣金类型',
            'equal_price_type' => '平级奖分红佣金类型',
            'is_alone' => '是否独立设置',
            'goods_id' => '股东等级',
            'goods_type' => '商品类型',
            'goods_boss_level_list' => '比例设置',
            'goods_over_level_list' => '比例设置',
            'goods_equal_level_list' => '比例设置'
        ];
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-20
     * @Time: 17:30
     * @Note: 设置商品经销的公共函数
     * @return bool
     */


    public function saveBossGoodsSetting()
    {
        if (!is_array($this->goods_boss_level_list)) {
            return false;
        }

        if (!$this->is_alone) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',

            ];
        }
        $boss_goods = BossGoods::findOne(['goods_id' => $this->goods_id, 'is_delete' => 0, 'goods_type' => $this->goods_type]);
        if (!$boss_goods) {
            $boss_goods = new BossGoods();
            $boss_goods->goods_id = $this->goods_id;
            $boss_goods->goods_type;
            $boss_goods->mall_id = \Yii::$app->mall->id;
        }
        $boss_goods->is_alone = $this->is_alone;
        $boss_goods->price_type = $this->price_type;
        $boss_goods->equal_price_type = $this->equal_price_type;

        if (!$boss_goods->save()) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '保存失败',
                'error' => $boss_goods->getErrors()
            ];
        }
        foreach ($this->goods_boss_level_list as $item) {
            $boss_goods_detail = BossGoodsDetail::findOne(['is_delete' => 0, 'boss_goods_id' => $boss_goods->id, 'level' => $item['level']]);
            if (!$boss_goods_detail) {
                $boss_goods_detail = new BossGoodsDetail();
                $boss_goods_detail->mall_id = \Yii::$app->mall->id;
                $boss_goods_detail->goods_id = $this->goods_id;
                $boss_goods_detail->level = $item['level'];
            }
            $boss_goods_detail->boss_goods_id = $boss_goods->id;
            $boss_goods_detail->over_boss_price = isset($item['over']['over_boss_price']) ? $item['over']['over_boss_price'] : 0;
            $boss_goods_detail->boss_price = isset($item['boss']['boss_price']) ? $item['boss']['boss_price'] : 0;
            $boss_goods_detail->equal_price = isset($item['equal']['equal_price']) ? $item['equal']['equal_price'] : 0;
            $boss_goods_detail->save();

        }

        return ['code' => ApiCode::CODE_SUCCESS, 'msg' => '保存成功'];
    }


    public function getBossGoodsSetting()
    {
        $is_enable = BossSetting::getValueByKey(BossSetting::IS_ENABLE);
        if (!$is_enable) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '未启用股东插件',
            ];
        }
        $setting['goods_id'] = $this->goods_id;
        $setting['is_enable'] = $is_enable;
        $is_equal = BossSetting::getValueByKey(BossSetting::IS_ENABLE);
        if ($is_equal) {
            $setting['is_equal'] = $is_equal;
        }
        $level_list = BossLevel::find()->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->orderBy('level ASC')->asArray()->all();
        if (!count($level_list)) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '请先添加股东等级',
            ];
        }
        $boss_goods = BossGoods::findOne(['is_delete' => 0, 'goods_id' => $this->goods_id, 'goods_type' => CommonOrderDetail::TYPE_MALL_GOODS]);
        if (!$boss_goods) {
            $setting['is_alone'] = 0;
            $setting['equal_price_type'] = 0;
            $setting['price_type'] = 0;
        } else {
            $setting['is_alone'] = $boss_goods->is_alone;
            $setting['equal_price_type'] = $boss_goods->equal_price_type;
            $setting['price_type'] = $boss_goods->price_type;
        }


        $goods_boss_level_list = [];
        foreach ($level_list as $item) {
            $newItem = [];
            $newItem['equal']['equal_price'] = '';
            $newItem['boss']['boss_price'] = '';
            $newItem['over']['over_boss_price'] = '';
            if ($boss_goods) {
                $detail = BossGoodsDetail::findOne(['boss_goods_id' => $boss_goods->id, 'level' => $item['level'], 'is_delete' => 0]);
                if ($detail) {
                    $newItem['equal']['equal_price'] = $detail->equal_price;
                    $newItem['boss']['boss_price'] = $detail->boss_price;
                    $newItem['over']['over_boss_price'] = $detail->over_boss_price;
                }
            }
            $newItem['level'] = $item['level'];
            $newItem['level_name'] = $item['name'];
            $goods_boss_level_list[] = $newItem;
        }


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'setting' => $setting,
                'level_list' => $level_list,

                'goods_boss_level_list' => $goods_boss_level_list
            ]
        ];
    }
}