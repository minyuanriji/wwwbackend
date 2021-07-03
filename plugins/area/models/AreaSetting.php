<?php

namespace app\plugins\area\models;

use app\models\BaseActiveRecord;
use Yii;
use yii\base\Model;

/**
 * This is the model class for table "{{%plugin_area_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $key
 * @property string $value
 * @property int $created_at 创建时间
 * @property int $updated_at 修改时间
 * @property int $is_delete 是否删除 0--未删除 1--已删除
 * @property int $deleted_at 删除时间
 */
class AreaSetting extends BaseActiveRecord
{
    const IS_ENABLE = 'is_enable'; // 是否启用
    const IS_LEVEL = 'is_level'; // 是否走级差
    const IS_EQUAL = 'is_equal'; // 是否启用平级
    const PROVINCE_PRICE = 'province_price'; // 省代佣金
    const CITY_PRICE = 'city_price'; // 市代佣金
    const DISTRICT_PRICE = 'district_price';//区代佣金；
    const TOWN_PRICE = 'town_price';//镇代佣金；
    const IS_APPLY = 'is_apply';//是否申请；
    const IS_CHECK = 'is_check';//是否审核；
    const PROTOCOL = 'protocol';//协议
    const COMPUTE_TYPE = 'compute_type';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_area_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'key', 'value'], 'required'],
            [['mall_id', 'created_at', 'updated_at', 'is_delete', 'deleted_at'], 'integer'],
            [['value'], 'string'],
            [['key'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'Mall ID',
            'key' => 'Key',
            'value' => 'Value',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
            'is_delete' => '是否删除 0--未删除 1--已删除',
            'deleted_at' => '删除时间',
        ];
    }

    /**
     * @param null $key
     * @return bool|string|void
     */
    public static function getValueByKey($key = null, $mall_id = null)
    {
        if (!$key) {
            return false;
        }

        if (!$mall_id) {
            $mall_id = Yii::$app->mall->id;
        }
        $model = AreaSetting::findOne(['mall_id' => $mall_id, 'is_delete' => 0, 'key' => $key]);

        if ($model) {
            return $model->value;
        }
        return false;
    }

    public static function strToNumber($key, $str)
    {
        $default = [
            'is_apply', 'is_enable', 'is_check', 'is_equal', 'is_level', 'compute_type', 'province_price', 'city_price', 'town_price', 'district_price', 'town_price'
        ];
        if (in_array($key, $default)) {
            return round($str, 2);
        }
        return $str;
    }

    /**
     * 获取经销配置，并组成key-value数组
     * @param $mallId
     * @return array
     */
    public static function getData($mallId)
    {
        $list = self::find()->where(['mall_id' => $mallId, 'is_delete' => 0])->all();

        $newList = [];
        /* @var self[] $list */
        foreach ($list as $item) {
            $newList[$item->key] = self::strToNumber($item->key, Yii::$app->serializer->decode($item->value));
        }
        return $newList;
    }

}
