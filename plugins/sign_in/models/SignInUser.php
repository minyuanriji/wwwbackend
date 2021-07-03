<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件-签到用户model类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:40
 */

namespace app\plugins\sign_in\models;

use app\models\BaseActiveRecord;
use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%plugin_sign_in_user}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $total 累计签到时间
 * @property int $continue 连续签到时间
 * @property int $is_remind 是否开启签到提醒
 * @property int $created_at
 * @property int $is_delete
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $continue_start 连续签到的起始日期
 * @property User $user
 */
class SignInUser extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_sign_in_user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'created_at', 'updated_at'], 'required'],
            [['mall_id', 'user_id', 'total', 'continue', 'is_remind', 'is_delete'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at', 'continue_start'], 'safe'],
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
            'user_id' => 'User ID',
            'total' => '累计签到时间',
            'continue' => '连续签到时间',
            'is_remind' => '是否开启签到提醒',
            'created_at' => 'Created At',
            'is_delete' => 'Is Delete',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'continue_start' => '连续签到的起始日期',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getUserSignInStatus($userId,$startTime,$endTime){

        return self::find()->where(['between','created_at',$startTime,$endTime])->andWhere(['user_id'=>$userId])->asArray()->one();
    }

    //批量新增
    public function insertArray($list){

        //1.批量插入一
        $col = ['mall_id', 'user_id', 'number', 'type', 'day', 'status', 'created_at', 'updated_at','token','award_id','remark'];
        return Yii::$app->db->createCommand()->batchInsert('jxmall_plugin_sign_in', $col, $list)->execute();

    }
}
