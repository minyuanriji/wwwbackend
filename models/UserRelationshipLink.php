<?php
namespace app\models;


class UserRelationshipLink extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_relationship_link}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'parent_id', 'left', 'right'], 'required'],
            [['is_delete', 'delete_reason'], 'safe']
        ];
    }

    /**
     * 获取所有上级的用户ID
     * @return array
     */
    public function getParentIds(){
        $rows = self::find()->andWhere([
            "AND",
            ['<', 'left', $this->left],
            ['>', 'right', $this->right]
        ])->select(["user_id"])->asArray()->all();
        $parentIds = [];
        if($rows){
            foreach($rows as $row){
                $parentIds[] = $row['user_id'];
            }
        }
        return $parentIds;
    }
}