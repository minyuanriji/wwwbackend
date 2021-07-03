<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-01
 * Time: 21:49
 */

namespace app\forms\mall\goods;


use app\models\BaseModel;
use app\models\GoodsDistribution;


class GoodsShareForm extends BaseModel
{
    public $goods_id;
    public $goods_attr_id;
    public $distribution_commission_first;
    public $distribution_commission_second;
    public $distribution_commission_third;
    public $level;

    public function rules()
    {
        return [
            [['distribution_commission_first', 'distribution_commission_second', 'distribution_commission_third'], 'number', 'min' => 0],
            [['distribution_commission_first', 'distribution_commission_second', 'distribution_commission_third'], 'default', 'value' => 0],
            [['goods_id', 'goods_attr_id', 'level'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'distribution_commission_first' => '一级分销佣金',
            'distribution_commission_second' => '二级分销佣金',
            'distribution_commission_third' => '三级分销佣金',
            'level' => '分销商等级',
        ];
    }

    /**
     * @throws \Exception
     */
    public function save()
    {
        if (!$this->validate()) {
            throw new \Exception($this->responseErrorMsg());
        }
    }
}
