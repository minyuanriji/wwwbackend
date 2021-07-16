<?php
namespace app\models;


class Apps extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%apps}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'platform', 'type', 'version_code', 'version_name', 'download_link', 'created_at'], 'required'],
            [['is_delete', 'deleted_at', 'updated_at', 'content'], 'safe']
        ];
    }
}