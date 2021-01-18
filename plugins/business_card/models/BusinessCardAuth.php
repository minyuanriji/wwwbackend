<?php

namespace app\plugins\business_card\models;

use app\models\BaseActiveRecord;
use app\models\User;
use app\plugins\business_card\models\BusinessCardDepartment;
use Yii;


/**
 * 权限
 * This is the model class for table "{{%plugin_business_card_auth}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $department_id
 * @property int $position_id
 * @property int $role_id
 * @property string $permissions
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 * @property int $is_delete
 *
 * @property User $user
 * @property BusinessCardDepartment $department
 * @property BusinessCardPosition $position
 */
class BusinessCardAuth extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_business_card_auth}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'department_id','position_id', 'created_at', 'deleted_at', 'updated_at','role_id'], 'required'],
            [['mall_id', 'user_id', 'role_id','position_id', 'created_at', 'deleted_at', 'updated_at', 'is_delete'], 'integer'],
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
            'department_id' => '部门ID',
            'position_id' => '职位ID',
            'role_id' => 'role_id',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getDepartment()
    {
        return $this->hasOne(BusinessCardDepartment::className(), ['id' => 'department_id']);
    }

    public function getPosition()
    {
        return $this->hasOne(BusinessCardPosition::className(), ['id' => 'position_id']);
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
        if(isset($params["user_id"]) && !empty($params["user_id"])){
            $query->andWhere(["user_id" => $params["user_id"]]);
        }
        if(isset($params["department_id"]) && !empty($params["department_id"])){
            $query->andWhere(["department_id" => $params["department_id"]]);
        }
        if(isset($params["position_id"]) && !empty($params["position_id"])){
            $query->andWhere(["position_id" => $params["position_id"]]);
        }
        if(isset($params["role_id"]) && !empty($params["role_id"])){
            $query->andWhere(["role_id" => $params["role_id"]]);
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
        if(isset($params["department"])){
            $query->with(["department"]);
        }
        if(isset($params["position"])){
            $query->with(["position"]);
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
        $businessCardAuthModel = new BusinessCardAuth();
        if(isset($data["id"]) && !empty($data["id"])){
            $businessCardAuthModel = self::findOne($data["id"]);
        }
        if(isset($data["mall_id"])){
            $businessCardAuthModel->mall_id = $data["mall_id"];
        }
        if(isset($data["user_id"])){
            $businessCardAuthModel->user_id = $data["user_id"];
        }
        if(isset($data["department_id"])){
            $businessCardAuthModel->department_id = $data["department_id"];
        }
        if(isset($data["position_id"])){
            $businessCardAuthModel->position_id = $data["position_id"];
        }
        if(isset($data["role_id"])){
            $businessCardAuthModel->role_id = $data["role_id"];
        }
        $result = $businessCardAuthModel->save();
        if($result !== false){
            return $businessCardAuthModel->id;
        }else{
            return false;
        }
    }

    /**
     * 批量增加
     * @param $rows
     * @return int
     * @throws \yii\db\Exception
     */
    public function batchAdd($rows){
        //print_r($rows);exit;
        $columns = $this->attributes();
        unset($columns[0],$columns[6],$columns[8],$columns[9],$columns[10]);
        //print_r($columns);exit;
        return Yii::$app->db->createCommand()->batchInsert(self::tableName(), $columns, $rows)->execute();
    }

}
