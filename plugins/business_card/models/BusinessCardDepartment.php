<?php

namespace app\plugins\business_card\models;

use app\models\BaseActiveRecord;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%plugin_business_card_department}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $pid
 * @property int $sort
 * @property string $name
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $is_delete
 * @property User $user
 * @property OrderDetail $orderDetail
 */
class BusinessCardDepartment extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_business_card_department}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name'], 'required'],
            [['mall_id','pid', 'is_delete','sort'], 'integer'],
            [['name'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
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
            'pid' => 'PID',
            'sort' => 'sort',
            'name' => '部门名',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
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
        if(isset($params["pid"]) && !empty($params["pid"])){
            $query->andWhere(["pid" => $params["pid"]]);
        }
        if(isset($params["name"]) && !empty($params["name"])){
            $query->andWhere(["name" => $params["name"]]);
        }
        //排序
        $orderByColumn = isset($params["sort_key"]) ? $params["sort_key"] : "id";
        $orderByType = isset($params["sort_val"]) ? $params["sort_val"] : " desc";
        $orderBy = $orderByColumn." ".$orderByType;
        if(!empty($fields)){
            $query->select($fields);
        }
        $pagination = null;
        if(isset($params["limit"]) && isset($params["page"])){
            $query->page($pagination, $params['limit'], $params['page']);
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
        $businessCardDepartmentModel = new BusinessCardDepartment();
        if(isset($data["id"]) && !empty($data["id"])){
            $businessCardDepartmentModel = self::findOne($data["id"]);
            if(empty($businessCardDepartmentModel)){
                return false;
            }
            if($businessCardDepartmentModel->is_delete == self::YES){
                return false;
            }
            $data["mall_id"] = $businessCardDepartmentModel->mall_id;
        }
        $businessCardDepartmentModel->mall_id = $data["mall_id"];
        if(isset($data["pid"])){
            $businessCardDepartmentModel->pid = $data["pid"];
        }
        if(isset($data["sort"])){
            $businessCardDepartmentModel->sort = $data["sort"];
        }
        if(isset($data["name"])){
            $businessCardDepartmentModel->name = $data["name"];
        }
        if(isset($data["is_delete"])){
            $businessCardDepartmentModel->is_delete = $data["is_delete"];
        }
        return $businessCardDepartmentModel->save();
    }
}
