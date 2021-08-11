<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%queue_data}}".
 *
 * @property int $id
 * @property int $queue_id 队列返回值
 * @property string $token
 */
class QueueData extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%queue_data}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['queue_id'], 'integer'],
            [['token'], 'required'],
            [['token'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'queue_id' => '队列返回值',
            'token' => 'Token',
        ];
    }

    /**
     * 将队列id和token存数据表
     * @param $queue_id
     * @param $token
     */
    public static function add($queue_id, $token)
    {
        $model = new self();
        $model->queue_id = $queue_id;
        $model->token = $token;
        $model->save();
    }

    /**
     * 根据token获取队列的id
     * @param $token
     * @return int|null
     */
    public static function select($token)
    {
        /* @var self $model*/
        $model = self::find()->where(['token' => $token])->orderBy(['id' => SORT_DESC])->one();
        if (!$model) {
            return null;
        }
        return $model->queue_id;
    }
}
