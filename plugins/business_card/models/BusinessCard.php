<?php

namespace app\plugins\business_card\models;

use app\models\BaseActiveRecord;
use app\models\User;
use Yii;

/**
 * 名片model
 * This is the model class for table "{{%plugin_business_card}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property string|null $head_img 头像
 * @property string|null $full_name 姓名
 * @property int $position_id 职位id
 * @property int $department_id 部门id
 * @property string|null $mobile 手机号码
 * @property string|null $email 邮箱
 * @property string|null $wechat_qrcode 微信二维码
 * @property string|null $address 地址
 * @property string|null $company_name 公司名称
 * @property string|null $company_address 公司地址
 * @property string|null $landline 座机
 * @property integer|null $likes 点赞数
 * @property integer|null $visitors 浏览数
 * @property string|null $introduction 简介
 * @property string|null $images 图片，json格式存储
 * @property string|null $videos 视频
 * @property string|null $voices 语音
 * @property integer|null $status 状态
 * @property integer|null $is_auth 是否授权手机号0否1是
 * @property string|null $auth_mobile 授权手机号
 * @property int $is_delete
 * @property int $created_at 创建时间
 * @property int $deleted_at 删除时间
 * @property int $updated_at 修改时间
 *
 * @property BusinessCardTag $tag
 * @property User $user
 * @property BusinessCardDepartment $department
 * @property BusinessCardPosition $position
 */
class BusinessCard extends BaseActiveRecord
{

    const STATUS_ON = 1;
    const STATUS_OFF = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_business_card}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id'], 'required'],
            [['mall_id','user_id', 'is_delete','position_id','department_id','created_at', 'deleted_at', 'updated_at', 'likes', 'visitors', 'status', 'is_auth'], 'integer'],
            [['head_img', 'full_name', 'mobile','email', 'wechat_qrcode','company_name','company_address','landline','auth_mobile'],'string']
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
            'head_img' => '头像',
            'full_name' => '姓名',
            'department_id' => '部门id',
            'position_id' => '职位id',
            'mobile' => '手机号码',
            'email' => '邮箱',
            'wechat_qrcode' => '微信二维码',
            'address' => '地址',
            'company_name' => '公司名',
            'company_address' => '公司地址',
            'landline' => '座机',
            'likes' => '点赞数',
            'visitors' => '浏览数',
            'introduction' => '简介',
            'images' => '图片，json格式存储',
            'videos' => '视频',
            'voices' => '语音',
            'status' => '状态',
            'is_auth' => '是否授权',
            'auth_mobile' => '授权手机号',
            'is_delete' => 'Is Delete',
            'created_at' => '创建时间',
            'deleted_at' => '删除时间',
            'updated_at' => '修改时间',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getTag()
    {
        return $this->hasMany(BusinessCardTag::className(), ['bcid' => 'id'])->andWhere(["is_delete" => self::NO]);
    }

    public function getPosition()
    {
        return $this->hasOne(BusinessCardPosition::class, ['id' => 'position_id']);
    }

    public function getDepartment()
    {
        return $this->hasOne(BusinessCardDepartment::class, ['id' => 'department_id']);
    }

    public function afterSave($insert, $changedAttributes)
    {

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
        if(isset($params["not_user_id"]) && !empty($params["not_user_id"])){
            $query->andWhere(['!=',"user_id" , $params["not_user_id"]]);
        }

        if(isset($params["keywords"]) && !empty($params["keywords"])){
            $query->andWhere(["like","full_name",$params["keywords"]]);
        }

        if(isset($params["status"]) && !empty($params["status"])){
            $query->andWhere(["status" => $params["status"]]);
        }

        if(isset($params["is_auth"]) && !empty($params["is_auth"])){
            $query->andWhere(["is_auth" => $params["is_auth"]]);
        }
        if(isset($data["auth_mobile"]) && !empty($params["auth_mobile"])){
            $query->andWhere(["auth_mobile" => $params["auth_mobile"]]);
        }
        //排序
        $orderByColumn = isset($params["sort_key"]) ? $params["sort_key"] : "id";
        $orderByType = isset($params["sort_val"]) ? $params["sort_val"] : " desc";
        $orderBy = $orderByColumn.$orderByType;
        if(!empty($fields)){
            $query->select($fields);
        }

        if(isset($params["return_count"])){
            return $query->count();
        }

        $pagination = null;
        if(isset($params["limit"]) && isset($params["page"])){
            $query->page($pagination, $params['limit'], $params['page']);
        }

        $query->with(["user","tag","position","department"])->asArray()->orderBy($orderBy);

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
        $busineCardModel = new BusinessCard();
        if(isset($data["id"]) && !empty($data["id"])){
            $busineCardModel = self::findOne($data["id"]);
            if(empty($busineCardModel)){
                return false;
            }
            if($busineCardModel->is_delete == self::YES){
                return false;
            }
            $data["user_id"] = $busineCardModel->user_id;
            $data["mall_id"] = $busineCardModel->mall_id;
        }
        $busineCardModel->user_id = $data["user_id"];
        $busineCardModel->mall_id = $data["mall_id"];
        if(isset($data["status"])){
            $busineCardModel->status = $data["mall_id"];
        }
        if(isset($data["head_img"])){
            $busineCardModel->head_img = $data["head_img"];
        }
        if(isset($data["full_name"])){
            $busineCardModel->full_name = $data["full_name"];
        }
        if(isset($data["status"])){
            $busineCardModel->status = $data["status"];
        }
        if(isset($data["is_auth"])){
            $busineCardModel->is_auth = $data["is_auth"];
        }
        if(isset($data["auth_mobile"])){
            $busineCardModel->auth_mobile = $data["auth_mobile"];
        }
        if(isset($data["position_id"])){
            $busineCardModel->position_id = $data["position_id"];
        }
        if(isset($data["department_id"])){
            $busineCardModel->department_id = $data["department_id"];
        }
        if(isset($data["mobile"])){
            $busineCardModel->mobile = $data["mobile"];
        }
        if(isset($data["email"])){
            $busineCardModel->email = $data["email"];
        }
        if(isset($data["wechat_qrcode"])){
            $busineCardModel->wechat_qrcode = $data["wechat_qrcode"];
        }
        if(isset($data["address"])){
            $busineCardModel->address = $data["address"];
        }
        if(isset($data["introduction"])){
            $busineCardModel->introduction = $data["introduction"];
        }
        if(isset($data["company_name"])){
            $busineCardModel->company_name = $data["company_name"];
        }
        if(isset($data["company_address"])){
            $busineCardModel->company_address = $data["company_address"];
        }
        if(isset($data["landline"])){
            $busineCardModel->landline = $data["landline"];
        }
        if(isset($data["images"]) && !empty($data["images"])){
            $busineCardModel->images = is_array($data["images"]) ? json_encode($data["images"]) : $data["images"];
        }
        if(isset($data["videos"])){
            $busineCardModel->videos = $data["videos"];
        }
        if(isset($data["voices"])){
            $busineCardModel->voices = $data["voices"];
        }
        if(isset($data["likes"])){
            $busineCardModel->likes = intval($busineCardModel->likes) + $data["likes"];
        }
        if(isset($data["visitors"])){
            $busineCardModel->visitors = intval($busineCardModel->visitors) + $data["visitors"];
        }
        if(isset($data["is_delete"])){
            $busineCardModel->is_delete = $data["is_delete"];
        }
        if(!$busineCardModel->save()){
            return false;
        }
        return $busineCardModel->id;
    }
}
