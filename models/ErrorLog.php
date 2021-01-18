<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%error_log}}".
 *
 * @property int $id
 * @property string|null $error_key 关键词
 * @property string|null $data 错误信息
 * @property int $created_at 创建时间
 */
class ErrorLog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%error_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at'], 'integer'],
            [['data'], 'string'],
            [['error_key'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'error_key'  => '关键词',
            'data'       => '错误信息',
            'created_at' => '创建时间',
        ];
    }

    /**
     * @param $error_key
     * @param $data
     * @return false
     */
    public function store($error_key, $data)
    {
        if (!$this->is_open()) {
            return false;
        }

        if (is_array($data)) {
            $data = json_encode($data);
        }
        $ErrorLog             = new ErrorLog();
        $ErrorLog->error_key  = $error_key;
        $ErrorLog->data       = $data;
        $ErrorLog->created_at = time();
        $ErrorLog->save();
    }

    /**
     * @return bool
     */
    public function is_open()
    {
        $env = [
            'dev', 'test','production'
        ];

        if (YII_DEBUG != true) {
            return false;
        }

        if (!in_array(YII_ENV, $env)) {
            return false;
        }

        return true;
    }
}
