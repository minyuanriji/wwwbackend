<?php

namespace app\plugins\smart_shop\models;

use app\models\BaseActiveRecord;
use app\models\Store;
use app\plugins\mch\models\Mch;

class Merchant extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_smartshop_merchant}}';
    }

    public function rules(){
        return [
            [['mall_id', 'bsh_mch_id', 'created_at', 'updated_at'], 'required'],
            [['is_delete'], 'safe']
        ];
    }

    /**
     * 获取商户数据
     * @return \yii\db\ActiveQuery
     */
    public function getMch(){
        return $this->hasOne(Mch::class, ["id" => "bsh_mch_id"]);
    }

    /**
     * 获取门店
     * @return \yii\db\ActiveQuery
     */
    public function getStore(){
        return $this->hasOne(Store::class, ["mch_id" => "bsh_mch_id"]);
    }
}