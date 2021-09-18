<?php
namespace app\plugins\shopping_voucher\models;

use app\models\BaseActiveRecord;

class ShoppingVoucherTargetAlibabaDistributionGoods extends BaseActiveRecord{

    
    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_shopping_voucher_target_alibaba_distribution_goods}}';
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mall_id','goods_id','sku_id','created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['is_delete'],'in','range' => [0,1]],
            [['name', 'cover_pic'], 'string'],
            [['voucher_price'], 'number']
        ];
    }

}
