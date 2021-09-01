<?php
namespace app\plugins\Shopping_voucher\models;

use app\logic\IntegralLogic;
use app\models\BaseActiveRecord;
use app\models\User;
use app\models\user\User as UserModel;
use app\models\mysql\{UserParent,UserChildren};
use Exception;
use Yii;

class VoucherGoods extends BaseActiveRecord{

    
    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_voucher_goods}}';
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mall_id','goods_id','created_at', 'updated_at', 'deleted_at'], 'integer'],
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
            'goods_id' => '商品ID',
            'deleted_at' => '删除时间',
            'updated_at' => '更新时间',
            'created_at' => '创建时间'
        ];
    }
}
