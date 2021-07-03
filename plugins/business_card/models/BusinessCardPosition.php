<?php

namespace app\plugins\business_card\models;

use app\models\BaseActiveRecord;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use Yii;
use yii\db\Exception;

/**
 * This is the model class for table "{{%plugin_business_card_position}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $bcpid
 * @property string $name
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $is_delete
 * @property User $user
 * @property BusinessCardDepartment $department
 */
class BusinessCardPosition extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_business_card_position}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name'], 'required'],
            [['mall_id','bcpid', 'is_delete'], 'integer'],
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
            'bcpid' => '部门id',
            'name' => '职位名',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getDepartment()
    {
        return $this->hasOne(BusinessCardDepartment::class, ['id' => 'bcpid']);
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

        if(isset($params["bcpid"]) && !empty($params["bcpid"])){
            $query->andWhere(["bcpid" => $params["bcpid"]]);
        }

        if(isset($params["name"]) && !empty($params["name"])){
            $query->andWhere(["name" => $params["name"]]);
        }

        //排序
        $orderByColumn = isset($params["sort_key"]) ? $params["sort_key"] : "id";
        $orderByType = isset($params["sort_val"]) ? $params["sort_val"] : " desc";
        $orderBy = $orderByColumn.$orderByType;
        if(!empty($fields)){
            $query->select($fields);
        }

        $pagination = null;
        if(isset($params["limit"]) && isset($params["page"])){
            $query->page($pagination, $params['limit'], $params['page']);
        }

        if(isset($params["department"])){
            $query->with(["department"]);
        }
        $query->asArray()->orderBy($orderBy);

        //查询单条数据
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
        $businessCardPositionModel = new BusinessCardPosition();
        if(isset($data["id"]) && !empty($data["id"])){
            $businessCardPositionModel = self::findOne($data["id"]);
            if(empty($businessCardPositionModel)){
                return false;
            }
            if($businessCardPositionModel->is_delete == self::YES){
                return false;
            }
            $data["mall_id"] = $businessCardPositionModel->mall_id;
        }
        $businessCardPositionModel->mall_id = $data["mall_id"];
        if(isset($data["bcpid"])){
            $businessCardPositionModel->bcpid = $data["bcpid"];
        }
        if(isset($data["name"])){
            $businessCardPositionModel->name = $data["name"];
        }
        if(isset($data["is_delete"])){
            $businessCardPositionModel->is_delete = $data["is_delete"];
        }
        $result = $businessCardPositionModel->save();
        return $result;
    }
}
