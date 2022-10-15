<?php
namespace app\plugins\income_log\models;

use app\models\BaseActiveRecord;

class Setting extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_incomelog_setting}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'created_at', 'updated_at'], 'required'],
            [['name', 'value'], 'safe']
        ];
    }

    public static function getSettings(){
        $settings = [];
        try {
            $rows = static::find()->asArray()
                ->select(["name", "value"])->all();
            foreach($rows as $row){
                $settings[$row['name']] = $row['value'];
            }
            return $settings;
        }catch (\Exception $e){
            throw $e;
        }
    }
}
