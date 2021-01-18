<?php

namespace app\plugins\business_card\models;

use app\models\BaseActiveRecord;
use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%plugin_business_card_customer}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $user_type 客户类型0普通客户1意向客户2比较客户3待成交客户4已成交客户
 * @property int $operate_id 操作人id
 * @property int $status 状态0待授权1新增线索2跟进中3成交
 * @property int $is_tag 是否生成了自动标签0否1是
 * @property string $basic_info 基础信息，json格式存储
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 * @property int $is_delete
 *
 * @property array $basicInfo
 * @property User $user
 * @property User $operator
 */
class BusinessCardCustomer extends BaseActiveRecord
{

    /** @var int 客户类型0普通客户1意向客户2比较客户3待成交客户4已成交客户 */
    const USER_TYPE_ORDINARY = 0;
    const USER_TYPE_INTENT = 1;
    const USER_TYPE_COMPARE = 2;
    const USER_TYPE_WAIT_CLINCH = 3;
    const USER_TYPE_ALREADY_CLINCH = 4;

    public static $userTypeData = [
        self::USER_TYPE_ORDINARY => "新客户",
        self::USER_TYPE_INTENT => "意向客户",
        self::USER_TYPE_COMPARE => "比较客户",
        self::USER_TYPE_WAIT_CLINCH => "待成交客户",
        self::USER_TYPE_ALREADY_CLINCH => "已成交客户",
    ];

    //boss雷达统计
    public static $statArr =[
        self::USER_TYPE_ORDINARY => "新增客户",
        self::USER_TYPE_INTENT => "咨询客户",
        self::USER_TYPE_COMPARE => "跟进客户",
    ];

    /** @var int 状态0新客户1新增线索2跟进中3成交 */
    const STATUS_AUTH = 0;
    const STATUS_NEW_CLUE = 1;
    const STATUS_FOLLOWING = 2;
    const STATUS_DEAL = 3;

    public static $statusData = [
        self::STATUS_AUTH => "待授权",
        self::STATUS_NEW_CLUE => "新增线索",
        self::STATUS_FOLLOWING => "跟进中",
        self::STATUS_DEAL => "已成交",
    ];

    public static $userTypeToStatus = [
        self::USER_TYPE_ORDINARY => self::STATUS_AUTH,
        self::USER_TYPE_INTENT => self::STATUS_FOLLOWING,
        self::USER_TYPE_COMPARE => self::STATUS_FOLLOWING,
        self::USER_TYPE_WAIT_CLINCH => self::STATUS_FOLLOWING,
        self::USER_TYPE_ALREADY_CLINCH => self::STATUS_DEAL,
    ];

    const IS_TAG_NO = 0;
    const IS_TAG_YES= 1;

    public static $keywords = "";

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_business_card_customer}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id','operate_id'], 'required'],
            [['mall_id','is_tag', 'user_id', 'user_type', 'operate_id', 'status','created_at', 'deleted_at', 'updated_at', 'is_delete'], 'integer'],
            [['basic_info'],'string']
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
            'operate_id' => '操作人id',
            'user_type' => '客户类型',
            'is_tag' => '是否自动生成标签',
            'basic_info' => '基础信息',
            'status' => '状态',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getBasicInfo()
    {
        $basicInfo = [];
        if(!empty($this->basic_info)){
            $basicInfo = json_decode($this->basic_info,true);
        }
        $this->basic_info = $basicInfo;
        return $basicInfo;
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getOperator()
    {
        return $this->hasOne(User::class, ['id' => 'operate_id']);
    }

    /**
     * 获取数据
     * @param $params
     * @param $fields 字段
     * @return \app\models\BaseActiveQuery|array|\yii\db\ActiveRecord|\yii\db\ActiveRecord[]|null
     */
    public static function getData($params,$fields = []){
        $returnData = [];
        $query = self::find()->alias("bcc")->where(["bcc.is_delete" => self::NO]);
        if(isset($params["keywords"]) && !empty($params["keywords"])){
            $query->leftJoin(['u' => User::tableName()], 'u.id = bcc.user_id');
            //$query->leftJoin(['bct' => BusinessCardTag::tableName()], 'u.id = bcc.user_id');
            $query->andWhere(["or",
                ["like","u.nickname",$params["keywords"]],
                ["like","bcc.basic_info",$params["keywords"]],
                //["like","bct.name",$params["keywords"]],
            ]);
        }
        if(isset($params["id"]) && !empty($params["id"])){
            $params["is_one"] = 1;
            $query->andWhere(["bcc.id" => $params["id"]]);
        }
        if(isset($params["mall_id"]) && !empty($params["mall_id"])){
            $query->andWhere(["bcc.mall_id" => $params["mall_id"]]);
        }
        if(isset($params["user_id"]) && !empty($params["user_id"])){
            $query->andWhere(["bcc.user_id" => $params["user_id"]]);
        }
        if(isset($params["operate_id"]) && !empty($params["operate_id"])){
            $query->andWhere(["bcc.operate_id" => $params["operate_id"]]);
        }
        if(isset($params["user_type"]) && !empty($params["user_type"])){
            $query->andWhere(["bcc.user_type" => $params["user_type"]]);
        }
        if(isset($params["status"]) && !empty($params["status"])){
            $query->andWhere(["bcc.status" => $params["status"]]);
        }
        if(isset($params["filter_time_start"]) && isset($params["filter_time_end"]) && !empty($params["filter_time_start"]) && !empty($params["filter_time_end"])){
            $query->andFilterWhere(['between','bcc.updated_at',$params["filter_time_start"], $params["filter_time_end"]]);
        }
        //排序
        $orderByColumn = isset($params["sort_key"]) ? $params["sort_key"] : "bcc.id";
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
            //if(!isset($params["keywords"]) || empty($params["keywords"])){
                $query->with(["user"]);
            //}
        }
        if(isset($params["operator"])){
            //if(!isset($params["keywords"]) || empty($params["keywords"])){
                $query->with(["operator"]);
            //}
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
        $businessCardCustomerModel = new BusinessCardCustomer();
        if(isset($data["id"]) && !empty($data["id"])){
            $businessCardCustomerModel = self::findOne($data["id"]);
        }
        if(isset($data["mall_id"])){
            $businessCardCustomerModel->mall_id = $data["mall_id"];
        }
        if(isset($data["user_id"])){
            $businessCardCustomerModel->user_id = $data["user_id"];
        }
        if(isset($data["user_type"])){
            $businessCardCustomerModel->user_type = $data["user_type"];
        }
        if(isset($data["operate_id"])){
            $businessCardCustomerModel->operate_id = $data["operate_id"];
        }
        if(isset($data["is_delete"])){
            $businessCardCustomerModel->is_delete = $data["is_delete"];
        }
        if(isset($data["is_tag"])){
            $businessCardCustomerModel->is_tag = $data["is_tag"];
        }
        if(isset($data["status"])){
            $businessCardCustomerModel->status = $data["status"];
        }
        if(isset($data["basic_info"])){
            $businessCardCustomerModel->basic_info = $data["basic_info"];
        }
        $result = $businessCardCustomerModel->save();
        if($result !== false){
            return $businessCardCustomerModel->id;
        }else{
            return false;
        }
    }
}
