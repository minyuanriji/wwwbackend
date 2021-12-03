<?php

namespace app\plugins\taolijin\models;

use app\models\BaseActiveRecord;

class TaolijinCats extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_taolijin_cats}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'parent_id', 'name', 'sort', 'updated_at', 'created_at'], 'required'],
            [['sort', 'deleted_at', 'is_delete', 'pic_url'], 'safe']
        ];
    }

    public function getParent()
    {
        return $this->hasOne(TaolijinCats::class, ['id' => 'parent_id']);
    }

    public function getChild()
    {
        return $this->hasMany(TaolijinCats::class, ['parent_id' => 'id'])->andWhere(['is_delete' => 0]);
    }
}








