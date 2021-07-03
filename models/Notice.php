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
 * This is the model class for table "{{%notice}}".
 *
 * @property int $id
 * @property int $title
 * @property int $content
 * @property int $is_delete
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 */
class Notice extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%notice}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'content'], 'required'],
            [['is_delete','created_at','updated_at','deleted_at'], 'integer'],
            [['title','content'], 'string'],
        ];
    }
}