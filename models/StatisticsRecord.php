<?php
/**
 * Created by PhpStorm.
 * User: 阿源
 * Date: 2020/10/22
 * Time: 10:10
 */
namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%statistics_record}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $type
 * @property int $num
 * @property int $date
 * @property string $remark
 * @property int $created_at
 * @property int update_type
 */
class StatisticsRecord extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%statistics_record}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'type'], 'required'],
            [['date','update_type'], 'integer'],
            [['num'],'number'],
            [['remark'], 'string'],
        ];
    }
}