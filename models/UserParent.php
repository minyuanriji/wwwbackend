<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%user_parent}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $parent_id
 * @property int $updated_at
 * @property int $created_at
 * @property int $deleted_at
 * @property int $is_delete
 * @property int $level 层级
 * @property User $parent
 * @property User $user
 */
class UserParent extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_parent}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'parent_id', 'level'], 'required'],
            [['mall_id', 'user_id', 'parent_id', 'updated_at', 'created_at', 'deleted_at', 'is_delete', 'level'], 'integer'],
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
            'parent_id' => 'Parent ID',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'level' => '层级',
        ];
    }

    public function getParent()
    {
        return $this->hasOne(User::class, ['id' => 'parent_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * 新增
     * @param $mall_id
     * @param $user_id
     * @param $parent_id
     * @param $level
     * @return bool
     */
    public static function add($mall_id,$user_id,$parent_id,$level){
        $userParentModel = new UserParent();
        $userParentModel->mall_id = $mall_id;
        $userParentModel->user_id = $user_id;
        $userParentModel->parent_id = $parent_id;
        $userParentModel->created_at = time();
        $userParentModel->level = $level;
        return $userParentModel->save();
    }

    /**
     * 获取某用户到N层级的全部上级
     *
     * @Author bing
     * @DateTime 2020-09-21 10:40:18
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param integer $user_id
     * @param integer $level
     * @return array
     */
    public static function getParentsIdsByUser($user_id, $level=0){
        $query = self::find()->select('parent_id')
        ->where(array('user_id'=>$user_id,'is_delete'=>0,'mall_id'=>Yii::$app->mall->id));
        if($level > 0) $query->andWhere(array('<=','level',$level));
        return $query->orderBy('level ASC')->asArray()->column();
     }
 
     /**
      * 获取指定用户的上级ID
      *
      * @Author bing
      * @DateTime 2020-09-21 15:08:41
      * @copyright: Copyright (c) 2020 广东七件事集团
      * @param integer $user_id
      * @return int
      */
     public static function getParentIdByUser(int $user_id,$level=1){
         return self::find()->select('parent_id')
         ->where(array('user_id'=>$user_id,'is_delete'=>0,'mall_id'=>Yii::$app->mall->id,'level'=>$level))
         ->scalar();
     }
}
