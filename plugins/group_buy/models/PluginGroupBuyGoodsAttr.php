<?php
/**
 * xuyaoxiang
 * 2020/08/24
 */

namespace app\plugins\group_buy\models;

use Yii;
use app\models\GoodsAttr;
/**
 * This is the model class for table "{{%plugin_group_buy_goods_attr}}".
 *
 * @property int $id
 * @property int $attr_id 属性表id
 * @property float $price 价格
 */
class PluginGroupBuyGoodsAttr extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_group_buy_goods_attr}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['attr_id', 'group_buy_price'], 'required'],
            [['attr_id', 'stock'], 'integer'],
            [['stock'], 'default', 'value' => 0]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'attr_id'         => '属性表id',
            'group_buy_price' => '价格',
            'stock' => '拼团规格库存',
        ];
    }

    public function getAttr(){
        return $this->hasOne(GoodsAttr::className(), ['id' => 'attr_id']);
    }
}
