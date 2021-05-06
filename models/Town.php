<?php

namespace app\models;

use Yii;
use yii\base\BaseObject;

/**
 * This is the model class for table "{{%town}}".
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
class Town extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%town}}';
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
            'district_id' => 'City ID',
            'adcode' => 'Adcode',
            'name' => 'Name',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
        ];
    }
}
