<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销商基础类
 * Author: zal
 * Date: 2020-04-16
 * Time: 10:45
 */

namespace app\forms\mall\distribution;

use app\core\ApiCode;
use app\forms\common\distribution\DistributionLevelCommon;
use app\component\jobs\ChangeDistributionLevelJob;
use app\models\BaseModel;
use app\models\DistributionSetting;
use app\models\GoodsCats;
use app\models\GoodsWarehouse;

class BasicForm extends BaseModel
{
    public $level;
    public $is_rebate;
    public $price_type;
    public $first;
    public $second;
    public $third;
    public $share_condition;
    public $condition;
    public $auto_share_val;
    public $share_goods_status;
    public $share_goods_warehouse_id;
    public $pay_type;
    public $cash_max_day;
    public $min_money;
    public $cash_service_fee_rate;
    public $agree;
    public $content;
    public $pic_url_apply;
    public $pic_url_status;
    public $pic_url_home_head;
    public $become_condition;
    public $cat_list;
    public $is_show_share_level;

    public function rules()
    {
        return [
            [['level', 'is_rebate', 'price_type', 'share_condition', 'condition', 'pay_type', 'cash_max_day',
                'min_money', 'cash_service_fee_rate'], 'required'],
            [['level', 'is_rebate', 'price_type', 'share_condition', 'condition',
                'become_condition', 'share_goods_status', 'is_show_share_level'], 'integer'],
            [['first', 'second', 'third', 'auto_share_val',
                'min_money', 'cash_service_fee_rate'], 'number', 'min' => 0],
            [['cash_max_day'], 'default', 'value' => -1],
            [['cash_max_day'], 'number', 'min' => -1],
            [['agree', 'content', 'pic_url_apply', 'pic_url_status', 'pic_url_home_head'], 'string'],
            [['agree', 'content', 'pic_url_apply', 'pic_url_status', 'pic_url_home_head'], 'trim'],
            [['first', 'second', 'third'], function ($attr, $params) {
                switch ($this->level) {
                    case 3:
                        if ($this->third == '') {
                            $this->addError($attr, '请输入三级分销佣金');
                        }
                        if ($this->second == '') {
                            $this->addError($attr, '请输入二级分销佣金');
                        }
                        if ($this->first == '') {
                            $this->addError($attr, '请输入一级分销佣金');
                        }
                        break;
                    case 2:
                        if ($this->second == '') {
                            $this->addError($attr, '请输入二级分销佣金');
                        }
                        if ($this->first == '') {
                            $this->addError($attr, '请输入一级分销佣金');
                        }
                        break;
                    case 1:
                        if ($this->first == '') {
                            $this->addError($attr, '请输入一级分销佣金');
                        }
                        break;
                    default:
                        break;
                }
            }, 'skipOnEmpty' => false, 'skipOnError' => false],
            [['share_goods_warehouse_id'], function ($attr, $params) {
                if ($this->share_goods_status == 2 && !($this->$attr && !empty($this->$attr))
                    && $this->become_condition == 2) {
                    $this->addError($attr, $this->getAttributeLabel($attr) . '必须选择');
                }
            }, 'skipOnEmpty' => false, 'skipOnError' => false],
            [['cat_list'], function ($attr, $params) {
                if ($this->share_goods_status == 3 && !($this->$attr && !empty($this->$attr))
                    && $this->become_condition == 2) {
                    $this->addError($attr, $this->getAttributeLabel($attr) . '必须选择');
                }
            }, 'skipOnEmpty' => false, 'skipOnError' => false],
        ];
    }

    public function attributeLabels()
    {
        return [
            'level' => '分销层级',
            'is_rebate' => '分销内购',
            'price_type' => '分销佣金类型',
            'first' => '一级佣金',
            'second' => '二级佣金',
            'third' => '三级佣金',
            'share_condition' => '成为分销商条件',
            'condition' => '成为下线条件',
            'auto_share_val' => '消费自动成为分销商',
            'share_goods_status' => '购买商品自动成为分销商',
            'share_goods_warehouse_id' => '指定的商品',
            'pay_type' => '提现方式',
            'cash_max_day' => '每日提现上限',
            'min_money' => '最少提现金额',
            'cash_service_fee_rate' => '提现手续费',
            'agree' => '申请协议',
            'content' => '用户须知',
            'pic_url_apply' => '申请页面背景图片',
            'pic_url_status' => '审核页面背景图片',
            'pic_url_home_head' => '首页背景图片'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $setList = [];
            if (!empty($this->cat_list)) {
                $list = [];
                foreach ($this->cat_list as $item) {
                    $list[] = $item['value'];
                }
                $this->cat_list = $list;
            }
            foreach ($this->attributes as $index => $item) {
                $setList[] = [
                    'key' => $index,
                    'value' => $item
                ];
            }
            DistributionSetting::setList(\Yii::$app->mall->id, $setList);
            \Yii::$app->queue->delay(0)->push(new ChangeDistributionLevelJob([
                'mall' => \Yii::$app->mall
            ]));
            return [
                'code' => 0,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => 1,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function search()
    {
        $list = DistributionSetting::getList(\Yii::$app->mall->id);
        if (isset($list[DistributionSetting::SHARE_GOODS_WAREHOUSE_ID]) && $list[DistributionSetting::SHARE_GOODS_WAREHOUSE_ID]) {
            $goodsList = GoodsWarehouse::findAll([
                'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id,
                'id' => $list[DistributionSetting::SHARE_GOODS_WAREHOUSE_ID]
            ]);
            $list['goods_list'] = [];
            if ($goodsList) {
                foreach ($goodsList as $goods) {
                    $list['goods_list'][] = [
                        'id' => $goods->id,
                        'name' => $goods->name,
                        'cover_pic' => $goods->cover_pic
                    ];
                }
            }
        }
        if (isset($list[DistributionSetting::CAT_LIST]) && $list[DistributionSetting::CAT_LIST]) {
            $catList = GoodsCats::findAll([
                'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id,
                'id' => $list[DistributionSetting::CAT_LIST]
            ]);
            $list['cat_list'] = [];
            if ($catList) {
                foreach ($catList as $cat) {
                    $list['cat_list'][] = [
                        'label' => $cat['name'],
                        'value' => $cat['id']
                    ];
                }
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $list
            ]
        ];
    }

    public function getGoodsDistributionConfig()
    {
        $level = DistributionSetting::get(\Yii::$app->mall->id, DistributionSetting::LEVEL);
        $shareArray = [
            [
                'label' => '一级分销',
                'value' => 'distribution_commission_first',
            ],
            [
                'label' => '二级分销',
                'value' => 'distribution_commission_second',
            ],
            [
                'label' => '三级分销',
                'value' => 'distribution_commission_third',
            ],
        ];
        array_splice($shareArray, $level);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'shareArray' => $shareArray,
                'distributionLevelList' => DistributionLevelCommon::getInstance()->getList(),
            ]
        ];
    }
}
