<?php

namespace app\plugins\business_card\models;

use app\models\BaseActiveRecord;
use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%plugin_business_card_customer_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $operate_id 操作人id
 * @property int $remark 备注
 * @property int $log_type 记录类型1添加商机2新增线索3新增跟进记录4拨打电话5私聊6修改设置7订单成交
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 * @property int $is_delete
 *
 * @property array $basicInfo
 * @property User $user
 * @property User $operator
 */
class BusinessCardCustomerLog extends BaseActiveRecord
{
    /** @var int 记录类型1添加商机2新增线索3新增跟进记录4拨打电话5私聊6修改设置7订单成交 */
    const LOG_TYPE_BUSINESS = 1;
    const LOG_TYPE_NEW_CLUE = 2;
    const LOG_TYPE_FOLLOW = 3;
    const LOG_TYPE_CALL = 4;
    const LOG_TYPE_PRIVATE_CHAT = 5;
    const LOG_TYPE_UPDATE_INFO = 6;
    const LOG_TYPE_ORDER_DEAL = 7;

    public static $remarks = [
        BusinessCardCustomer::USER_TYPE_INTENT => '开始跟进',
        BusinessCardCustomer::USER_TYPE_COMPARE => '开始比较',
        BusinessCardCustomer::USER_TYPE_WAIT_CLINCH => '待成交',
        BusinessCardCustomer::USER_TYPE_ALREADY_CLINCH => '订单成交',
    ];

    //状态对应的记录类型
    public static $statusToLogType = [
        BusinessCardCustomer::STATUS_AUTH => self::LOG_TYPE_BUSINESS,
        BusinessCardCustomer::STATUS_NEW_CLUE => self::LOG_TYPE_NEW_CLUE,
        BusinessCardCustomer::STATUS_FOLLOWING => self::LOG_TYPE_FOLLOW,
        BusinessCardCustomer::STATUS_DEAL => self::LOG_TYPE_ORDER_DEAL,
    ];

    public static $logTypes = [
        self::LOG_TYPE_BUSINESS => '添加商机',
        self::LOG_TYPE_NEW_CLUE => '新增线索',
        self::LOG_TYPE_FOLLOW => '新增跟进记录',
        self::LOG_TYPE_CALL => '拨打电话',
        self::LOG_TYPE_PRIVATE_CHAT => '私聊客户',
        self::LOG_TYPE_UPDATE_INFO => '客户设置',
        self::LOG_TYPE_ORDER_DEAL => '订单成交',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_business_card_customer_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id','operate_id'], 'required'],
            [['mall_id', 'user_id', 'operate_id','log_type','created_at', 'deleted_at', 'updated_at', 'is_delete'], 'integer'],
            [['remark'],'string']
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
            'log_type' => '记录类型',
            'remark' => '基础信息',
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
        if(isset($params["operate_id"]) && !empty($params["operate_id"])){
            $query->andWhere(["operate_id" => $params["operate_id"]]);
        }
        if(isset($params["log_type"]) && !empty($params["log_type"])){
            $query->andWhere(["log_type" => $params["log_type"]]);
        }
        if(isset($params["filter_time_start"]) && isset($params["filter_time_end"]) && !empty($params["filter_time_start"]) && !empty($params["filter_time_end"])){
            $query->andFilterWhere(['between','created_at',$params["filter_time_start"], $params["filter_time_end"]]);
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
        if(isset($params["user"])){
            $query->with(["user"]);
        }
        if(isset($params["operator"])){
            $query->with(["operator"]);
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
        $businessCardCustomerLogModel = new BusinessCardCustomerLog();
        if(isset($data["id"]) && !empty($data["id"])){
            $businessCardCustomerLogModel = self::findOne($data["id"]);
            if(empty($businessCardCustomerLogModel)){
                return false;
            }
            if($businessCardCustomerLogModel->is_delete == self::YES){
                return false;
            }
            $data["mall_id"] = $businessCardCustomerLogModel->mall_id;
        }
        $businessCardCustomerLogModel->mall_id = $data["mall_id"];
        if(isset($data["user_id"])){
            $businessCardCustomerLogModel->user_id = $data["user_id"];
        }
        if(isset($data["operate_id"])){
            $businessCardCustomerLogModel->operate_id = $data["operate_id"];
        }
        if(isset($data["remark"])){
            $businessCardCustomerLogModel->remark = $data["remark"];
        }
        if(isset($data["log_type"])){
            $businessCardCustomerLogModel->log_type = $data["log_type"];
        }
        if(isset($data["is_delete"])){
            $businessCardCustomerLogModel->is_delete = $data["is_delete"];
        }
        $result = $businessCardCustomerLogModel->save();
        return $result;
    }
}
