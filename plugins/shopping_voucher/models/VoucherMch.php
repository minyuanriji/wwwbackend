<?php
namespace app\plugins\Shopping_voucher\models;

use app\models\BaseActiveRecord;

class VoucherMch extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_voucher_mch}}';
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mall_id','mch_id', 'ratio', 'created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['is_delete'],'in','range' => [0,1]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(){
        return [
            'id' => 'ID',
            'mall_id' => '商城ID',
            'mch_id' => '商户ID',
            'ratio' => '比例',
            'deleted_at' => '删除时间',
            'updated_at' => '更新时间',
            'created_at' => '创建时间'
        ];
    }
}
