<?php

namespace app\models;

use app\events\UserInfoEvent;
use app\handlers\RelationHandler;

/**
 * This is the model class for table "{{%user_info}}".
 *
 *
 * @property int $id ID
 * @property int $mall_id
 * @property int $mch_id
 * @property int $user_id
 * @property string $openid
 * @property string $unionid
 * @property string $platform
 * @property string $platform_data
 * @property string $remark
 * @property int $is_delete
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 * @property int $deleted_at 删除时间
 * @property User $user
 * @property Mall $mall
 *
 */
class UserInfo extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_info}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id'], 'required'],
            [['is_delete', 'mall_id', 'mch_id', 'is_delete', 'created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['unionid','openid','platform', 'platform_data', 'remark'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => '商城id',
            'mch_id' => '商户id',
            'user_id' => '用户id',
            'openid' => '公众号平台用户openid',
            'unionid' => '平台唯一标识',
            'platform' => '平台名',
            'platform_data' => '平台数据',
            'remark' => '备注',
            'is_delete' => '是否删除',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'deleted_at' => '删除时间',
        ];
    }

    public static function getOneUserInfo($where)
    {
        $isUser = 0;
        if(isset($where["user"])){
            $isUser = 1;
            unset($where["user"]);
        }
        $query = self::find()->where($where);
        if($isUser == 1){
            $query->with(["user"]);
        }
        return $query->one();
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getMall()
    {
        return $this->hasMany(Mall::className(), ['user_id' => 'user_id']);
    }
}
