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
 * This is the model class for table "{{%statistics_browse_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $type
 * @property int $browse_type
 * @property int $user_id
 * @property string $user_ip
 * @property int $created_at
 */
class StatisticsBrowseLog extends BaseActiveRecord
{

    const EVEN_STATISTICS_LOG = 'statistics_log';//浏览记录存储
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%statistics_browse_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'type', 'created_at', 'browse_type'], 'required'],
            [['user_id'], 'integer'],
            [['user_ip'], 'string'],
        ];
    }
}