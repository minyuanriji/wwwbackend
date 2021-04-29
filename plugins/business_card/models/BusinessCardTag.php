<?php

namespace app\plugins\business_card\models;

use app\models\BaseActiveRecord;
use app\models\User;
use Yii;

/**
 * 标签
 * This is the model class for table "{{%plugin_business_card_tag}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $add_user_id 新增此标签的用户id
 * @property int $bcid 名片id
 * @property string $name
 * @property int $likes 点赞数
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 * @property int $is_delete
 * @property int $is_diy 是否自定义标签
 *
 * @property BusinessCard $businessCard
 * @property User $user
 * @property User $addUser
 */
class BusinessCardTag extends BaseActiveRecord
{

    const IS_DIY_YES = 1;
    const IS_DIY_NO = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_business_card_tag}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id','bcid'], 'required'],
            [['mall_id','is_diy','user_id', 'add_user_id', 'likes','created_at', 'deleted_at', 'updated_at', 'is_delete'], 'integer'],
            [['name'],'string']
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
            'add_user_id' => '新增此标签的用户id',
            'bcid' => '名片id',
            'name' => '标签名',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getBusinessCard()
    {
        return $this->hasOne(BusinessCard::class, ['id' => 'bcid']);
    }

    public function getAddUser()
    {
        return $this->hasOne(User::class, ['id' => 'add_user_id']);
    }

    /**
     * 获取数据
     * @param $params
     * @param $fields 字段
     * @return \app\models\BaseActiveQuery|array|\yii\db\ActiveRecord|\yii\db\ActiveRecord[]|null
     */
    public static function getData($params,$fields = []){
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
        if(isset($params["is_diy"])){
            $query->andWhere(["is_diy" => $params["is_diy"]]);
        }
        if(isset($params["bcid"])){
            $query->andWhere(["bcid" => $params["bcid"]]);
        }
        if(isset($params["name"])){
            $query->andWhere(["name" => $params["name"]]);
        }
        //排序
        $orderByColumn = isset($params["sort_key"]) ? $params["sort_key"] : "id";
        $orderByType = isset($params["sort_val"]) ? $params["sort_val"] : " desc";
        $orderBy = $orderByColumn.$orderByType;

        if(!empty($fields)){
            $query->select($fields);
        }
        if(isset($params["limit"])){
            $query->limit($params["limit"]);
        }

        $query->asArray()->orderBy($orderBy);

        if(isset($params["is_one"]) && $params["is_one"] == 1){
            $list = $query->one();
        }else{
            $list = $query->all();
        }
        return $list;
    }

    public static function getCount($params){
        $query = self::find()->where(["is_delete" => self::NO]);
        if(isset($params["bcid"])){
            $query->andWhere(["bcid" => $params["bcid"]]);
        }
        if(isset($params["count"])){
            return $query->count();
        }
        return $query->all();
    }

    /**
     * 操作数据库
     * @param $data
     * @return bool
     */
    public static function operateData($data){
        $busineCardTagModel = new BusinessCardTag();
        if(isset($data["id"]) && !empty($data["id"])){
            $busineCardTagModel = self::findOne($data["id"]);
            if(empty($busineCardTagModel)){
                return false;
            }
            if($busineCardTagModel->is_delete == self::YES){
                return false;
            }
            $data["user_id"] = $busineCardTagModel->user_id;
            $data["mall_id"] = $busineCardTagModel->mall_id;
        }
        $busineCardTagModel->user_id = $data["user_id"];
        $busineCardTagModel->mall_id = $data["mall_id"];
        if(isset($data["add_user_id"])){
            $busineCardTagModel->add_user_id = $data["add_user_id"];
        }
        if(isset($data["bcid"])){
            $busineCardTagModel->bcid = $data["bcid"];
        }
        if(isset($data["name"])){
            $busineCardTagModel->name = $data["name"];
        }
        if(isset($data["likes"])){
            $busineCardTagModel->likes = intval($busineCardTagModel->likes) + $data["likes"];
        }
        if(isset($data["is_diy"])){
            $busineCardTagModel->is_diy = $data["is_diy"];
        }
        if(isset($data["is_delete"])){
            $busineCardTagModel->is_delete = $data["is_delete"];
        }
        return $busineCardTagModel->save();
    }
}
