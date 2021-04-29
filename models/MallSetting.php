<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 基础model
 * Author: zal
 * Date: 2020-04-08
 * Time: 15:12
 */

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%mall_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $key
 * @property string $value
 * @property int $is_delete
 */
class MallSetting extends BaseActiveRecord
{

    const SEND_TYPE = 'send_type';//配送方式

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%mall_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id'], 'required'],
            [['mall_id', 'is_delete'], 'integer'],
            [['key'], 'string', 'max' => 65],
            [['value'], 'string', 'max' => 255],
            [['name','setting_desc'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'           => 'ID',
            'mall_id'      => 'Mall ID',
            'key'          => 'Key',
            'value'        => 'Value',
            'is_delete'    => 'Is Delete',
            'name'         => '配置名称',
            'setting_desc' => '配置说明',
        ];
    }

    /**
     * @param null $key
     * @return bool|string|void
     */
    public static function getValueByKey($key, $mall_id)
    {
        if (!$key) {
            return false;
        }
        if (!$mall_id) {
            $mall_id = Yii::$app->mall->id;
        }

        if (!$mall_id) {
            return false;
        }
        $model = self::findOne(['mall_id' => $mall_id, 'is_delete' => 0, 'key' => $key]);

        if ($model) {
            return $model->value;
        }

        return false;
    }

    /**
     * @param $key
     * @param $mall_id
     * @return array|\yii\db\ActiveRecord|null
     */
    public function getOneBykey($key,$mall_id){

        return MallSetting::find()->where(['mall_id' => $mall_id, 'key' => $key, 'is_delete' => 0])->one();
    }

}
