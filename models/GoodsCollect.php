<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%goods_collect}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $goods_id
 * @property int $user_id
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $is_delete
 * @property Goods $goods
 */
class GoodsCollect extends  BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_collect}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'goods_id', 'user_id'], 'required'],
            [['mall_id', 'goods_id', 'user_id', 'created_at', 'updated_at', 'deleted_at', 'is_delete'], 'integer'],
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
            'goods_id' => 'Goods ID',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }


    public function getGoods(){
        return $this->hasOne(Goods::class,['id'=>'goods_id']);
    }

    /**
     * 获取同一类型商品的购买次数
     * @param $params
     * @param $fields 字段
     * @return \app\models\BaseActiveQuery|array|\yii\db\ActiveRecord|\yii\db\ActiveRecord[]|null
     */
    public static function getSameCatsGoodsCollectTotal($params,$fields = []){
        $returnData = [];
        $query = self::find()->where(["is_delete" => self::NO]);
        if(isset($params["id"]) && !empty($params["id"])){
            $params["is_one"] = 1;
            $query->andWhere(["id" => $params["id"]]);
        }
        if(isset($params["mall_id"]) && !empty($params["mall_id"])){
            $query->andWhere(["mall_id" => $params["mall_id"]]);
        }
        if(isset($params["user_id"]) && !empty($params["user_id"])){
            $query->andWhere(["user_id" => $params["user_id"]]);
        }
        if(isset($params["goods_id"]) && !empty($params["goods_id"])){
            $query->andWhere(["goods_id" => $params["goods_id"]]);
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
        if(isset($params["user"])){
            $query->with(["user"]);
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

}
