<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%town1}}".
 *
 * @property int $id
 * @property int|null $district_id
 * @property int|null $adcode
 * @property string|null $name
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 * @property int|null $is_delete
 */
class Town1 extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%town1}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['district_id', 'adcode', 'created_at', 'deleted_at', 'updated_at', 'is_delete'], 'integer'],
            [['name'], 'string', 'max' => 45],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'district_id' => 'District ID',
            'adcode' => 'Adcode',
            'name' => 'Name',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
        ];
    }
}
