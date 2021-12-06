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
            [['mall_id', 'parent_id', 'name', 'sort', 'updated_at', 'ali_type', 'created_at'], 'required'],
            [['sort', 'deleted_at', 'is_delete', 'pic_url', 'ali_custom_data', 'ali_cat_id'], 'safe']
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

    public function getParams(){
        $paramList = $this->ali_custom_data ? @json_decode($this->ali_custom_data, true) : [];
        $param = [];
        foreach($paramList as $item){
            $param[$item['name']] = $item['value'];
        }
        return $param;
    }
}








