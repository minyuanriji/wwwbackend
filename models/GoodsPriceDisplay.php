<?php

namespace app\models;

use app\services\ReturnData;
use Yii;

/**
 * This is the model class for table "{{%goods_price_display}}".
 *
 * @property int $id
 * @property string $name 自定义商品价格显示名称
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 * @property int $deleted_at 删除时间
 * @property int $sort 排序;由大到小
 * @property int $mall_id 商城id
 */
class GoodsPriceDisplay extends BaseActiveRecord
{
    use ReturnData;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_price_display}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['mall_id', 'name'], 'required', "on" => "store"],
            [['mall_id', 'id'], 'required', "on" => "destory"],
            [['created_at', 'updated_at', 'deleted_at', 'sort', 'mall_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '自定义商品价格显示名称',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'deleted_at' => '删除时间',
            'sort' => '排序;由大到小',
            'mall_id' => '商城id',
        ];
    }
}
