<?php

namespace app\plugins\business_card\models;

use app\models\BaseActiveRecord;
use Yii;
use yii\base\Model;

/**
 * This is the model class for table "{{%plugin_business_card_setting}}".
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
class BusinessCardSetting extends BaseActiveRecord
{
    const COMPANY_NAME = "company_name";
    const COMPANY_ADDRESS = "company_address";
    const CARD_TOKEN = "card_token";
    const COMPANY_LOGO = "company_logo";
    const COMPANY_IMG = "company_img";
    const TAG = "tag_list";
    const POSTER = "poster";
    const VIDEO_SIZE = "video_size";

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_business_card_setting}}';
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
        $model = BusinessCardSetting::findOne(['mall_id' => $mall_id, 'is_delete' => 0, 'key' => $key]);

        if ($model) {
            return $model->value;
        }
        return false;
    }

    public static function strToNumber($key, $str)
    {
        $default = [];
        if (in_array($key, $default)) {
            return round($str, 2);
        }
        return $str;
    }

    /**
     * 获取配置，并组成key-value数组
     * @param $mallId
     * @return array
     */
    public static function getData($mallId)
    {
        $list = self::find()->where(['mall_id' => $mallId, 'is_delete' => 0])->asArray()->all();

        $newList = [];
        /* @var self[] $list */
        foreach ($list as $item) {
            if($item["key"] == BusinessCardSetting::TAG){
                $item["value"] = json_decode($item["value"],true);
            }
            $newList[$item["key"]] = $item["value"];
        }

        return $newList;
    }

}
