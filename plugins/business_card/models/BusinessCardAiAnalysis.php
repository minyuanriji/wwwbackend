<?php

namespace app\plugins\business_card\models;

use app\models\BaseActiveRecord;
use app\models\User;
use Yii;

/**
 * 雷达ai分析
 * This is the model class for table "{{%plugin_business_card_ai_analysis}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $sales_active 销售主动值
 * @property int $website_promote 官网推广值
 * @property int $goods_promote 产品推广值
 * @property int $deal_ability 成交能力
 * @property int $customers_ability 获客能力
 * @property int $personal_appeal 个人魅力
 * @property int $average 平均值
 * @property int $total 总值
 * @property int $year 年
 * @property int $month 月
 * @property int $day 日
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 * @property int $is_delete
 *
 * @property User $user
 * @property BusinessCard $businessCard
 */
class BusinessCardAiAnalysis extends BaseActiveRecord
{
    public static $aiAnalysis = [
        "销售主动值",
        "官网推广值",
        "产品推广值",
        "成交能力",
        "获客能力",
        "个人魅力",
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_business_card_ai_analysis}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id'], 'required'],
            [['mall_id', 'user_id', 'sales_active','website_promote', 'goods_promote','deal_ability','customers_ability','personal_appeal',
              'average','year','month','day','created_at', 'deleted_at', 'updated_at', 'is_delete','total'], 'integer'],
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
            'sales_active' => '销售主动值',
            'website_promote' => '官网推广值',
            'goods_promote' => '产品推广值',
            'deal_ability' => '成交能力',
            'customers_ability' => '获客能力',
            'personal_appeal' => '个人魅力',
            'average' => '平均值',
            'year' => '年',
            'month' => '月',
            'day' => '日',
            'total' => '总数',
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
        return $this->hasOne(BusinessCard::class, ['user_id' => 'user_id'])->andWhere(["is_delete" => 0]);
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
        if(isset($params["user_id"]) && !empty($params["user_id"])){
            $query->andWhere(["user_id" => $params["user_id"]]);
        }
        if(isset($params["mall_id"]) && !empty($params["mall_id"])){
            $query->andWhere(["mall_id" => $params["mall_id"]]);
        }
        if(isset($params["year"]) && !empty($params["year"])){
            $query->andWhere(["year" => $params["year"]]);
        }
        if(isset($params["day"]) && !empty($params["day"])){
            $query->andWhere(["day" => $params["day"]]);
        }
        if(isset($params["month"]) && !empty($params["month"])){
            $query->andWhere(["month" => $params["month"]]);
        }
        if(isset($params["look_time"]) && !empty($params["look_time"])){
            $date_start = date("Y-m-d 00:00:00",strtotime($params["look_time"]));
            $date_end = date("Y-m-d 23:59:59",strtotime($params["look_time"]));
            $query->andFilterWhere(['between','updated_at',strtotime($date_start), strtotime($date_end)]);
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

        if(isset($params["return_sum"])){
            return $query->sum($params["return_sum"]);
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

    /**
     * 操作数据库
     * @param $data
     * @return bool
     */
    public static function operateData($data){
        $businessCardAiAnalysisModel = new BusinessCardAiAnalysis();
        if(isset($data["id"]) && !empty($data["id"])){
            $businessCardAiAnalysisModel = self::findOne($data["id"]);
            if(empty($businessCardAiAnalysisModel)){
                return false;
            }
            if($businessCardAiAnalysisModel->is_delete == self::YES){
                return false;
            }
            $data["mall_id"] = $businessCardAiAnalysisModel->mall_id;
        }
        if(isset($data["mall_id"])){
            $businessCardAiAnalysisModel->mall_id = $data["mall_id"];
        }
        if(isset($data["user_id"])){
            $businessCardAiAnalysisModel->user_id = $data["user_id"];
        }
        if(isset($data["sales_active"])){
            $businessCardAiAnalysisModel->sales_active = intval($businessCardAiAnalysisModel->sales_active) + intval($data["sales_active"]);
        }
        if(isset($data["website_promote"])){
            $businessCardAiAnalysisModel->website_promote = intval($businessCardAiAnalysisModel->website_promote) + intval($data["website_promote"]);
        }
        if(isset($data["goods_promote"])){
            $businessCardAiAnalysisModel->goods_promote = intval($businessCardAiAnalysisModel->goods_promote) + intval($data["goods_promote"]);
        }
        if(isset($data["sales_active"])){
            $businessCardAiAnalysisModel->deal_ability = intval($businessCardAiAnalysisModel->deal_ability) + intval($data["deal_ability"]);
        }
        if(isset($data["customers_ability"])){
            $businessCardAiAnalysisModel->customers_ability = intval($businessCardAiAnalysisModel->customers_ability) + intval($data["customers_ability"]);
        }
        if(isset($data["personal_appeal"])){
            $businessCardAiAnalysisModel->personal_appeal = intval($businessCardAiAnalysisModel->personal_appeal) + intval($data["personal_appeal"]);
        }
        if(isset($data["average"])){
            $businessCardAiAnalysisModel->average = intval($data["average"]);
        }
        if(isset($data["total"])){
            $businessCardAiAnalysisModel->total = intval($businessCardAiAnalysisModel->total) + $data["total"];
        }
        if(isset($data["is_delete"])){
            $businessCardAiAnalysisModel->is_delete = $data["is_delete"];
        }
        if(isset($data["year"])){
            $businessCardAiAnalysisModel->year = $data["year"];
        }
        if(isset($data["month"])){
            $businessCardAiAnalysisModel->month = $data["month"];
        }
        if(isset($data["day"])){
            $businessCardAiAnalysisModel->day = $data["day"];
        }
        $result = $businessCardAiAnalysisModel->save();
        return $result;
    }
}
