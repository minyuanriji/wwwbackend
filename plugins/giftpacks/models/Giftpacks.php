<?php
namespace app\plugins\giftpacks\models;

use app\models\BaseActiveRecord;

class Giftpacks extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_giftpacks}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'price', 'expired_at', 'cover_pic', 'pic_url', 'title', 'max_stock', 'descript', 'created_at', 'updated_at'], 'required'],
            [['purchase_limits_num', 'is_delete', 'group_enable', 'allow_currency',
              'integral_enable', 'integral_give_num', 'group_price', 'group_need_num', 'group_expire_time', 'profit_price',
              'score_enable', 'score_give_settings', 'detail', 'view_num'], 'safe']
        ];
    }
}




