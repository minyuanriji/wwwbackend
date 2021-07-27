<?php
namespace app\plugins\commission\models;

use app\models\BaseActiveRecord;
use app\models\Store;
use app\plugins\mch\models\Goods;

class CommissionRules extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_commission_rules}}';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'mch_id', 'item_type', 'item_id', 'json_params',
              'created_at', 'commission_type', 'updated_at', 'apply_all_item'], 'required'],
            [['mch_id', 'commission_type', 'apply_all_item', 'item_id', 'is_delete', 'created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['json_params'], 'string']
        ];
    }

    public function getItem()
    {
        //对象类型是二维码收款
        if($this->item_type == "checkout")
        {
            return $this->hasOne(Store::className(), ["id" => "item_id"]);
        }

        //对象类型是商品
        if($this->item_type == "goods")
        {
            return $this->hasOne(Goods::className(), ["id" => "item_id"]);
        }

        return null;
    }
}