<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%tag}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $cat_id 标签分类id
 * @property string $name 标签名称
 * @property int $type 标签类型
 * @property string $condition 条件
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $is_delete
 *
 * @property TagCategory $tagCategory
 */
class Tag extends BaseActiveRecord
{
    //价值分层
    const TYPE_VALUE_SLICE = 1;
    //生命周期
    const TYPE_LIFE_CYCLE = 2;
    //营销偏好
    const TYPE_MARKET_PREFERENCE = 3;
    //行为偏好
    const TYPE_BEHAVIOR_PREFERENCE = 4;

    public static $types = [
        self::TYPE_VALUE_SLICE => "价值分层",
        self::TYPE_LIFE_CYCLE => "生命周期",
        self::TYPE_MARKET_PREFERENCE => "营销偏好",
        self::TYPE_BEHAVIOR_PREFERENCE => "行为偏好",
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tag}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id'], 'required'],
            [['mall_id', 'cat_id', 'type', 'created_at', 'updated_at', 'deleted_at', 'is_delete'], 'integer'],
            [['name'], 'string', 'max' => 45],
            [['condition'], 'string', 'max' => 3000],
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
            'cat_id' => 'Cat ID',
            'name' => 'Name',
            'type' => 'Type',
            'condition' => 'Condition',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
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
        if(isset($params["cat_id"]) && !empty($params["cat_id"])){
            $query->andWhere(["cat_id" => $params["cat_id"]]);
        }
        if(isset($params["name"]) && !empty($params["name"])){
            $query->andWhere(["name" => $params["name"]]);
        }
        if(isset($params["type"]) && !empty($params["type"])){
            $query->andWhere(["type" => $params["type"]]);
        }
        if(isset($params["condition"]) && !empty($params["condition"])){
            $query->andWhere(["condition" => $params["condition"]]);
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
        if(isset($params["tagCategory"])){
            $query->with(["tagCategory"]);
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
        $tagModel = new Tag();
        if(isset($data["id"]) && !empty($data["id"])){
            $tagModel = self::findOne($data["id"]);
        }
        if(isset($data["mall_id"])){
            $tagModel->mall_id = $data["mall_id"];
        }
        if(isset($data["cat_id"])){
            $tagModel->cat_id = $data["cat_id"];
        }
        if(isset($data["name"])){
            $tagModel->name = $data["name"];
        }
        if(isset($data["type"])){
            $tagModel->type = $data["type"];
        }
        if(isset($data["condition"])){
            $tagModel->condition = $data["condition"];
        }
        $result = $tagModel->save();
        if($result !== false){
            return $tagModel->id;
        }else{
            return false;
        }
    }
}
