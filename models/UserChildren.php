<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%user_children}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $child_id
 * @property int $level 层级
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $is_delete
 * @property User $children
 */
class UserChildren extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_children}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'child_id', 'level'], 'required'],
            [['mall_id', 'user_id', 'child_id', 'level', 'created_at', 'updated_at', 'deleted_at', 'is_delete'], 'integer'],
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
            'child_id' => 'Child ID',
            'level' => '层级',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        //先新增网体所有用户
        $growth_team_user_count = UserGrowth::findOne(['user_id' => $this->user_id, 'keyword' => UserGrowth::KEY_TEAM_USER_COUNT, 'is_delete' => 0]);
        if (!$growth_team_user_count) {
            $growth_team_user_count = new UserGrowth();
            $growth_team_user_count->user_id = $this->user_id;
            $growth_team_user_count->mall_id = $this->mall_id;
            $growth_team_user_count->keyword = UserGrowth::KEY_TEAM_USER_COUNT;
        }
        $team_count = self::find()->where(['user_id' => $this->user_id, 'is_delete' => 0, 'mall_id' => $this->mall_id])->count();
        $growth_team_user_count->value = $team_count;
        $growth_team_user_count->save();

        //一级所有用户
        $growth_team_user_first_count = UserGrowth::findOne(['user_id' => $this->user_id, 'keyword' => UserGrowth::KEY_TEAM_USER_FIRST_COUNT, 'is_delete' => 0]);
        if (!$growth_team_user_first_count) {
            $growth_team_user_first_count = new UserGrowth();
            $growth_team_user_first_count->user_id = $this->user_id;
            $growth_team_user_first_count->mall_id = $this->mall_id;
            $growth_team_user_first_count->keyword = UserGrowth::KEY_TEAM_USER_FIRST_COUNT;
        }
        $team_first_count = self::find()->where(['user_id' => $this->user_id, 'is_delete' => 0, 'mall_id' => $this->mall_id, 'level' => 1])->count();
        $growth_team_user_first_count->value = $team_first_count;
        $growth_team_user_first_count->save();
        //一级

        return parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
    }

    /**
     * 新增
     * @param $mall_id
     * @param $user_id
     * @param $child_id
     * @param $level
     * @return bool
     */
    public static function add($mall_id,$user_id,$child_id,$level){
        $userChildrenModel = new UserChildren();
        $userChildrenModel->mall_id = $mall_id;
        $userChildrenModel->user_id = $user_id;
        $userChildrenModel->child_id = $child_id;
        $userChildrenModel->created_at = time();
        $userChildrenModel->level = $level;
        return $userChildrenModel->save();
    }

    public function getChildren()
    {
        return $this->hasOne(User::class, ['id' => 'child_id']);
    }
}
