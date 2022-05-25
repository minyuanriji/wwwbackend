<?php

namespace app\plugins\perform_distribution\models;


use app\models\BaseActiveRecord;
use app\models\Goods;

class PerformDistributionGoods extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_perform_distribution_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'created_at', 'updated_at'], 'required'],
            [['goods_id', 'is_delete'], 'integer']
        ];
    }

    /**
     * 获取商品数据对象
     * @return \yii\db\ActiveQuery
     */
    public function getGoods(){
        return $this->hasOne(Goods::class, ["id" => "goods_id"]);
    }

}
