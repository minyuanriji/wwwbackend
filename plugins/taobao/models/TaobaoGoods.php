<?php

namespace app\plugins\taobao\models;

use app\models\BaseActiveRecord;

class TaobaoGoods extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_taobao_goods}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'account_id', 'app_key', 'adzone_id', 'special_id', 'invite_code', 'goods_id', 'updated_at', 'created_at'], 'required'],
            [['url'], 'safe']
        ];
    }

}

