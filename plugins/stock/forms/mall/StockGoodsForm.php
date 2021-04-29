<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-19
 * Time: 16:59
 */

namespace app\plugins\stock\forms\mall;

use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\models\CommonOrder;
use app\models\CommonOrderDetail;
use app\models\Goods;
use app\models\GoodsAttr;
use app\plugins\stock\forms\common\StockLevelCommon;
use app\plugins\stock\models\StockGoods;
use app\plugins\stock\models\StockGoodsDetail;
use app\plugins\stock\models\StockLevel;
use app\plugins\stock\models\StockSetting;


class StockGoodsForm extends BaseModel
{
    public $goods_id;
    public $goods_agent_level_list;
    public $goods_equal_level_list;
    public $goods_over_level_list;
    public $goods_type = CommonOrderDetail::TYPE_MALL_GOODS;
    public $agent_price_type;
    public $equal_price_type;//佣金类型
    public $is_alone;

    public function rules()
    {
        return [

            [['goods_id', 'agent_price_type', 'equal_price_type', 'is_alone', 'goods_type'], 'integer'],
            [['goods_agent_level_list', 'goods_over_level_list', 'goods_equal_level_list'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'agent_price_type' => '团队分红佣金类型',
            'equal_price_type' => '平级奖分红佣金类型',
            'is_alone' => '是否独立设置',
            'goods_id' => '代理商等级',
            'goods_type' => '商品类型',
            'goods_agent_level_list' => '比例设置',
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


    public function saveAgentGoodsSetting()
    {
        if (!is_array($this->goods_agent_level_list)) {
            return false;
        }

        if (!$this->is_alone) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',

            ];
        }
        $agent_goods = StockGoods::findOne(['goods_id' => $this->goods_id, 'is_delete' => 0, 'goods_type' => $this->goods_type]);
        if (!$agent_goods) {
            $agent_goods = new StockGoods();
            $agent_goods->goods_id = $this->goods_id;
            $agent_goods->goods_type;
            $agent_goods->mall_id = \Yii::$app->mall->id;
        }
        $agent_goods->is_alone = $this->is_alone;
        $agent_goods->agent_price_type = $this->agent_price_type;
        $agent_goods->equal_price_type = $this->equal_price_type;

        if (!$agent_goods->save()) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '保存失败',
                'error' => $agent_goods->getErrors()
            ];
        }
        foreach ($this->goods_agent_level_list as $item) {
            $agent_goods_detail = StockGoodsDetail::findOne(['is_delete' => 0, 'agent_goods_id' => $agent_goods->id, 'level' => $item['level']]);
            if (!$agent_goods_detail) {
                $agent_goods_detail = new StockGoodsDetail();
                $agent_goods_detail->mall_id = \Yii::$app->mall->id;
                $agent_goods_detail->goods_id = $this->goods_id;
                $agent_goods_detail->level = $item['level'];
            }
            $agent_goods_detail->agent_goods_id = $agent_goods->id;
            $agent_goods_detail->over_agent_price = isset($item['over']['over_agent_price']) ? $item['over']['over_agent_price'] : 0;
            $agent_goods_detail->agent_price = isset($item['stock']['agent_price']) ? $item['stock']['agent_price'] : 0;
            $agent_goods_detail->equal_price = isset($item['equal']['equal_price']) ? $item['equal']['equal_price'] : 0;
            $agent_goods_detail->save();

        }

        return ['code' => ApiCode::CODE_SUCCESS, 'msg' => '保存成功'];
    }


    public function getAgentGoodsSetting()
    {
        $is_enable = StockSetting::getValueByKey(StockSetting::IS_ENABLE);
        if (!$is_enable) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '未启用代理商插件',
            ];
        }
        $setting['goods_id'] = $this->goods_id;
        $setting['is_enable'] = $is_enable;
        $is_equal = StockSetting::getValueByKey(StockSetting::IS_ENABLE);
        if ($is_equal) {
            $setting['is_equal'] = $is_equal;
        }
        $level_list = StockLevel::find()->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->orderBy('level ASC')->asArray()->all();
        if (!count($level_list)) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '请先添加代理商等级',
            ];
        }
        $agent_goods = StockGoods::findOne(['is_delete' => 0, 'goods_id' => $this->goods_id, 'goods_type' => CommonOrderDetail::TYPE_MALL_GOODS]);
        if (!$agent_goods) {
            $setting['is_alone'] = 0;
            $setting['equal_price_type'] = 0;
            $setting['agent_price_type'] = 0;
        } else {
            $setting['is_alone'] = $agent_goods->is_alone;
            $setting['equal_price_type'] = $agent_goods->equal_price_type;
            $setting['agent_price_type'] = $agent_goods->agent_price_type;
        }


        $goods_agent_level_list = [];
        foreach ($level_list as $item) {
            $newItem = [];
            $newItem['equal']['equal_price'] = '';
            $newItem['stock']['agent_price'] = '';
            $newItem['over']['over_agent_price'] = '';
            if ($agent_goods) {
                $detail = StockGoodsDetail::findOne(['agent_goods_id' => $agent_goods->id, 'level' => $item['level'], 'is_delete' => 0]);
                if ($detail) {
                    $newItem['equal']['equal_price'] = $detail->equal_price;
                    $newItem['stock']['agent_price'] = $detail->agent_price;
                    $newItem['over']['over_agent_price'] = $detail->over_agent_price;
                }
            }
            $newItem['level'] = $item['level'];
            $newItem['level_name'] = $item['name'];
            $goods_agent_level_list[] = $newItem;
        }


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'setting' => $setting,
                'level_list' => $level_list,

                'goods_agent_level_list' => $goods_agent_level_list
            ]
        ];
    }
}