<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%object_tag}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $object_id 对象id，用户，文章，视频等id
 * @property int $tag_id 标签id
 * @property int $cat_id 标签分类id
 * @property int $likes 点赞数
 * @property int $visitors 浏览数
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $is_delete
 */
class ObjectTag extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%object_tag}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id'], 'required'],
            [['mall_id', 'object_id', 'tag_id','cat_id', 'likes', 'visitors', 'created_at', 'updated_at', 'deleted_at', 'is_delete'], 'integer'],
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
            'object_id' => 'Object ID',
            'cat_id' => 'Cat ID',
            'tag_id' => 'Tag ID',
            'likes' => 'Likes',
            'visitors' => 'Visitors',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getTag()
    {
        return $this->hasOne(Tag::class, ['id' => 'tag_id']);
    }

    /**
     * 获取数据
     * @param $params
     * @param $fields 字段
     * @return \app\models\BaseActiveQuery|array|\yii\db\ActiveRecord|\yii\db\ActiveRecord[]|null
     */
    public static function getData($params,$fields = []){
        $returnData = [];
        $query = self::find()->where(["is_delete" => self::NO]);
        if(isset($params["id"]) && !empty($params["id"])){
            $params["is_one"] = 1;
            $query->andWhere(["id" => $params["id"]]);
        }
        if(isset($params["mall_id"]) && !empty($params["mall_id"])){
            $query->andWhere(["mall_id" => $params["mall_id"]]);
        }
        if(isset($params["object_id"]) && !empty($params["object_id"])){
            $query->andWhere(["object_id" => $params["object_id"]]);
        }
        if(isset($params["tag_id"]) && !empty($params["tag_id"])){
            $query->andWhere(["tag_id" => $params["tag_id"]]);
        }
        if(isset($params["cat_id"]) && !empty($params["cat_id"])){
            $query->andWhere(["cat_id" => $params["cat_id"]]);
        }
        //排序
        $orderByColumn = isset($params["sort_key"]) ? $params["sort_key"] : "id";
        $orderByType = isset($params["sort_val"]) ? $params["sort_val"] : " desc";
        $orderBy = $orderByColumn." ".$orderByType;
        if(!empty($fields)){
            $query->select($fields);
        }

        if(isset($params["return_count"])){
            return $query->count();
        }
        if(isset($params["group_by"])){
            $query->groupBy($params["group_by"]);
        }

        $pagination = null;
        if(isset($params["limit"]) && isset($params["page"])){
            $query->page($pagination, $params['limit'], $params['page']);
        }

        if(isset($params["tag"])){
            $query->with(["tag"]);
        }

        $query->asArray()->orderBy($orderBy);
        if(isset($params["is_one"]) && $params["is_one"] == 1){
            $list = $query->one();
            $returnData = $list;
        }else{
            $list = $query->all();
            if(isset($params["limit"]) && isset($params["page"])) {
                $returnData["list"] = $list;
                $returnData["pagination"] = $pagination;
            }else{
                $returnData = $list;
            }
        }
        return $returnData;
    }

    /**
     * 操作数据库
     * @param $data
     * @return bool
     */
    public static function operateData($data){
        $tagModel = new ObjectTag();
        if(isset($data["id"]) && !empty($data["id"])){
            $tagModel = self::findOne($data["id"]);
        }
        if(isset($data["mall_id"]) && !empty($data["mall_id"])){
            $tagModel->mall_id = $data["mall_id"];
        }
        if(isset($data["object_id"]) && !empty($data["object_id"])){
            $tagModel->object_id = $data["object_id"];
        }
        if(isset($data["tag_id"]) && !empty($data["tag_id"])){
            $tagModel->tag_id = $data["tag_id"];
        }
        if(isset($data["cat_id"]) && !empty($data["cat_id"])){
            $tagModel->cat_id = $data["cat_id"];
        }
        if(isset($params["likes"]) && !empty($params["likes"])){
            $tagModel->likes = intval($tagModel->likes) + intval($data["likes"]);
        }
        if(isset($params["visitors"]) && !empty($params["visitors"])){
            $tagModel->visitors = intval($tagModel->visitors) + intval($data["visitors"]);
        }
        $result = $tagModel->save();
        if($result !== false){
            return $tagModel->id;
        }else{
            return false;
        }
    }
}
