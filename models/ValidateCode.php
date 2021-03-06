<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%validate_code}}".
 *
 * @property int $id
 * @property string $target
 * @property string $code
 * @property int $created_at
 * @property int $updated_at
 * @property int $is_validated 是否已验证：0=未验证，1-已验证
 */
class ValidateCode extends BaseActiveRecord
{
    /**
     * 是否验证
     */
    const IS_VALIDATED_TRUE = 1;// 已验证
    const IS_VALIDATED_FALSE = 0;// 未验证

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%validate_code}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['target', 'code', 'created_at', 'updated_at'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['is_validated'], 'integer'],
            [['target'], 'string', 'max' => 11],
            [['code'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'target' => 'Target',
            'code' => 'Code',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'is_validated' => 'Is Validated',
        ];
    }
}
