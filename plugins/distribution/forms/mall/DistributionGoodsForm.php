<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-19
 * Time: 16:59
 */

namespace app\plugins\distribution\forms\mall;
use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsAttr;
use app\plugins\distribution\forms\common\DistributionLevelCommon;
use app\plugins\distribution\models\DistributionGoods;
use app\plugins\distribution\models\DistributionGoodsDetail;
use app\plugins\distribution\models\DistributionSetting;


class DistributionGoodsForm extends BaseModel
{
    public $goods_id;
    public $goods_attr_id;
    public $commission_first;
    public $commission_second;
    public $commission_third;
    public $level;
    public $goods_type = 'MALL_GOODS';
    public $attr_setting_type;
    public $share_type;
    public $distribution_level_list;
    public $attr;
    public $is_alone;

    public function rules()
    {
        return [
            [['commission_first', 'commission_second', 'commission_third'], 'number', 'min' => 0],
            [['commission_first', 'commission_second', 'commission_third'], 'default', 'value' => 0],
            [['goods_id', 'goods_attr_id', 'level', 'share_type', 'attr_setting_type', 'is_alone'], 'integer'],
            [['goods_type'], 'string'],
            [['distribution_level_list', 'attr'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'commission_first' => '一级分销佣金',
            'commission_second' => '二级分销佣金',
            'commission_third' => '三级分销佣金',
            'level' => '分销商等级',
            'goods_type' => '商品类型',
            'is_alone' => '是否开启独立分销'
        ];
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-20
     * @Time: 17:31
     * @Note:设置分销商品
     * @return array|string
     */
    public function setDistributionGoodsSetting()
    {
        if ($this->goods_type == DistributionGoodsDetail::TYPE_MALL_GOODS) {
            $goods = Goods::findOne(['id' => $this->goods_id, 'is_delete' => 0]);
            if (!$goods) {
                return ['code' => ApiCode::CODE_FAIL, 'msg' => '商品不存在,请先保存商品！'];
            }
            $distributionGoods = DistributionGoods::findOne(['goods_id' => $this->goods_id, 'goods_type' => DistributionGoods::TYPE_MALL_GOODS]);
            if (!$distributionGoods) {
                $distributionGoods = new DistributionGoods();
                $distributionGoods->goods_id = $this->goods_id;
                $distributionGoods->goods_type = DistributionGoods::TYPE_MALL_GOODS;
                $distributionGoods->mall_id = \Yii::$app->mall->id;
            }
            $distributionGoods->share_type = $this->share_type;
            $distributionGoods->attr_setting_type = $this->attr_setting_type;
            $distributionGoods->is_alone = $this->is_alone;
            $res = $distributionGoods->save();
            if (!$res) {
                return $this->responseErrorMsg($distributionGoods);
            }
            if ($this->attr_setting_type == 0) {
                $res = $this->setGoodsDistribution(0, $this->distribution_level_list, $distributionGoods->id);
                if (!$res) {
                    return ['code' => ApiCode::CODE_FAIL, 'msg' => '发生出错误'];
                }
            } else {
                foreach ($this->attr as $key => $attrLevelItem) {
                    if ($this->attr_setting_type == 1) {
                        $attr_id = "";
                        foreach ($attrLevelItem['attr_list'] as $item) {
                            $attr_id .= $item["attr_id"].":";
                        }
                        $attr_id = substr($attr_id,0,strlen($attr_id)-1);
                        $goods_attr = GoodsAttr::findOne(['goods_id' => $this->goods_id, 'sign_id' => $attr_id, 'is_delete' => 0]);
                        if ($goods_attr) {
                            $res = $this->setGoodsDistribution($goods_attr->id, $attrLevelItem['distribution_level_list'], $distributionGoods->id);
                            if (!$res) {
                                return ['code' => ApiCode::CODE_FAIL, 'msg' => '发生出错误'];
                            }
                        }
                    }
                }
            }

            return ['code' => ApiCode::CODE_SUCCESS, 'msg' => '保存成功'];
        }
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-20
     * @Time: 17:30
     * @Note: 设置商品分销的公共函数
     * @param $goodsAttrId
     * @param $distributionLevelList
     * @param $distribution_goods_id
     * @return bool
     */
    protected function setGoodsDistribution($goodsAttrId, $distributionLevelList, $distribution_goods_id)
    {
        if (!is_array($distributionLevelList)) {
            return false;
        }
        $list = [];

        $res = DistributionGoodsDetail::find()
            ->where(['goods_id' => $this->goods_id, 'goods_attr_id' => $goodsAttrId, 'is_delete' => 0, 'goods_type' => DistributionGoodsDetail::TYPE_MALL_GOODS])
            ->all();
        /* @var DistributionGoodsDetail[] $res */
        foreach ($res as $item) {
            $item->is_delete = 1;
            $list[$item->level] = $item;
        }

        /* @var DistributionGoodsDetail[] $list */
        foreach ($distributionLevelList as $i => $distributionLevel) {
            if (!isset($list[$distributionLevel['level']])) {
                $distributionGoodsDetail = new DistributionGoodsDetail();
                $distributionGoodsDetail->is_delete = 0;
                $distributionGoodsDetail->goods_id = $this->goods_id;
                $distributionGoodsDetail->goods_attr_id = $goodsAttrId;
            } else {
                $distributionGoodsDetail = $list[$distributionLevel['level']];
                $distributionGoodsDetail->is_delete = 0;
            }
            $distributionGoodsDetail->distribution_goods_id = $distribution_goods_id;
            $distributionGoodsDetail->commission_first = $distributionLevel['commission_first']??0;
            $distributionGoodsDetail->commission_second = $distributionLevel['commission_second']??0;
            $distributionGoodsDetail->commission_third = $distributionLevel['commission_third']??0;
            $distributionGoodsDetail->level = $distributionLevel['level'];
            if (!$distributionGoodsDetail->save()) {
                return false;
            }
        }
        return true;
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-20
     * @Time: 19:01
     * @Note:获取商品的分销设置
     * @return array|bool
     */
    public function getGoodsDistributionSetting()
    {
        if ($this->goods_type == DistributionGoodsDetail::TYPE_MALL_GOODS) {
            $goods = Goods::findOne(['id' => $this->goods_id]);
            if (!$goods) {
                return false;
            }
            $detail = [];
            $detail['goods']['use_attr'] = $goods->use_attr;

            $attrGroups = SerializeHelper::decode($goods->attr_groups);
            $detail['goods']['attr_groups'] = $attrGroups;

            $distributionGoods = DistributionGoods::findOne(['goods_id' => $this->goods_id, 'goods_type' => DistributionGoods::TYPE_MALL_GOODS]);
            if ($distributionGoods) {
                $detail['distribution_goods'] = $distributionGoods;
            } else {
                $detail['distribution_goods'] = null;
            }
            /**
             *
             * 1.取到商品的规格
             * 2.循环找出每个规格包含多少个等级的分销设置
             * 3.循环每个分销设置的详情
             * 4.组合成数组
             *
             */
            if ($goods->use_attr) {
                $attrGroups = SerializeHelper::decode($goods->attr_groups);
                $attrList = $goods->resetAttr($attrGroups);
                foreach ($goods->attr as $key => $attrItem) {
                    $detail['attr'][$key]['attr_list'] = $attrList[$attrItem['sign_id']];
                    $distributionGoodsLevelList = [];
                    $distributionGoodsList = DistributionGoodsDetail::find()
                        ->where(['goods_id' => $this->goods_id, 'goods_attr_id' => $attrItem->id, 'goods_type' => DistributionGoodsDetail::TYPE_MALL_GOODS])
                        ->all();
                    if ($distributionGoodsList) {
                        /**
                         * @var DistributionGoodsDetail $distributionGoodsList []
                         */
                        foreach ($distributionGoodsList as $distributionGoods) {
                            $distributionGoodsLevelList[] = [
                                'level' => $distributionGoods->level,
                                'commission_first' => $distributionGoods->commission_first,
                                'commission_second' => $distributionGoods->commission_second,
                                'commission_third' => $distributionGoods->commission_third,
                            ];
                        }
                    }
                    $detail['attr_distribution_level_setting_list'][$key]['distribution_level_list'] = $distributionGoodsLevelList;
                }
            }

            $goodsDistribution = $this->getGoodsDistribution($this->goods_id);
            if ($goodsDistribution) {
                $detail['distribution_level_setting_list'] = $goodsDistribution;
            }
            return $detail;
        }
        return false;

    }


   protected function getGoodsDistribution($goodsId, $asArray = false)
    {
        $goodsDistribution = DistributionGoodsDetail::find()->select([
            'commission_first', 'commission_second', 'commission_third', 'level'
        ])->where(['goods_id' => $goodsId, 'goods_attr_id' => 0, 'is_delete' => 0, 'goods_type' => DistributionGoodsDetail::TYPE_MALL_GOODS])
            ->asArray($asArray)->all();
        return $goodsDistribution;
    }

    public function getDistributionConfig()
    {
        $level = DistributionSetting::getValueByKey(DistributionSetting::LEVEL);
        $distributionLevelArray = [
            [
                'label' => '一级分销',
                'value' => 'commission_first',
            ],
            [
                'label' => '二级分销',
                'value' => 'commission_second',
            ],
            [
                'label' => '三级分销',
                'value' => 'commission_third',
            ],
        ];
        array_splice($distributionLevelArray, $level);
        $detail = $this->getGoodsDistributionSetting();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'distributionLevelArray' => $distributionLevelArray,
                'distributionLevelList' => DistributionLevelCommon::getInstance()->getList(),
                'detail' => $detail
            ]
        ];
    }
}