<?php

namespace app\plugins\business_card\models;

use app\models\BaseActiveRecord;
use app\models\User;
use Yii;

/**
 * 用户轨迹
 * This is the model class for table "{{%plugin_business_card_track_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $track_user_id 轨迹对象id
 * @property string $remark
 * @property string $ip
 * @property int $track_type 轨迹类型1商城首页2查看图文3产品4授权号码5转发名片6查看名片7查看视频8保存电话9点赞10收藏11评论12查看动态13查看资讯14查看直播15教育
 * @property int $model_id 模块id,如名片存的是名片id，列表显示0
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 * @property int $is_delete
 *
 * @property User $user
 * @property User $trackUser
 */
class BusinessCardTrackLog extends BaseActiveRecord
{

    /** @var int 轨迹类型1商城首页2查看图文3产品4授权号码5转发名片6查看名片7查看视频8保存电话9点赞10收藏11评论12查看动态13查看资讯14查看直播15教育 */
    const TRACK_TYPE_MALL_INDEX = 1;
    const TRACK_TYPE_GRAPHIC = 2;
    const TRACK_TYPE_GOODS = 3;
    const TRACK_TYPE_AUTH_MOBILE = 4;
    const TRACK_TYPE_FORWARD_CARD = 5;
    const TRACK_TYPE_LOOK_CARD = 6;
    const TRACK_TYPE_LOOK_VIDEO = 7;
    const TRACK_TYPE_SAVE_MOBILE = 8;
    const TRACK_TYPE_LIKE = 9;
    const TRACK_TYPE_COLLECT = 10;
    const TRACK_TYPE_COMMENT = 11;
    const TRACK_TYPE_LOOK_DYNAMIC = 12;

    const TRACK_TYPE_LIKE_TAG = -1;

    public static $trackTypeData = [
      self::TRACK_TYPE_MALL_INDEX => "查看你的商城",
      self::TRACK_TYPE_GRAPHIC => "查看你的图文",
      self::TRACK_TYPE_GOODS => "查看你的商品",
      self::TRACK_TYPE_AUTH_MOBILE => "授权号码",
      self::TRACK_TYPE_FORWARD_CARD => "转发你的名片",
      self::TRACK_TYPE_LOOK_CARD => "查看你的名片",
      self::TRACK_TYPE_LOOK_VIDEO => "查看你的视频",
      self::TRACK_TYPE_SAVE_MOBILE => "复制通讯录",
      self::TRACK_TYPE_LIKE => "给你的名片点赞",
      self::TRACK_TYPE_COLLECT => "收藏",
      self::TRACK_TYPE_COMMENT => "评论",
      self::TRACK_TYPE_LOOK_DYNAMIC => "查看你的动态",
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_business_card_track_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id','track_user_id'], 'required'],
            [['mall_id', 'user_id', 'track_user_id','track_type', 'model_id','created_at', 'deleted_at', 'updated_at', 'is_delete'], 'integer'],
            [['ip','remark'],'string']
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
            'track_user_id' => '轨迹对象id',
            'track_type' => '轨迹类型',
            'model_id' => '模型id',
            'remark' => '备注',
            'ip' => 'ip',
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

    public function geTrackUser()
    {
        return $this->hasOne(User::class, ['id' => 'track_user_id']);
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
        if(isset($params["track_user_id"]) && !empty($params["track_user_id"])){
            $query->andWhere(["track_user_id" => $params["track_user_id"]]);
        }
        if(isset($params["not_track_user_id"]) && !empty($params["not_track_user_id"])){
            $query->andWhere(["!=","track_user_id" , 0]);
        }
        if(isset($params["model_id"]) && !empty($params["model_id"])){
            $query->andWhere(["model_id" => $params["model_id"]]);
        }
        if(isset($params["track_type"]) && !empty($params["track_type"])){
            $query->andWhere(["track_type" => $params["track_type"]]);
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

        if(isset($params["group_by"])){
            $query->groupBy($params["group_by"]);
        }

        if(isset($params["return_count"])){
            return $query->count();
        }

        $pagination = null;
        if(isset($params["limit"]) && isset($params["page"])){
            $query->page($pagination, $params['limit'], $params['page']);
        }
        if(isset($params["user"])){
            $query->with(["user"]);
        }
        if(isset($params["track_user"])){
            $query->with(["trackUser"]);
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
        $businessCardTrackLogModel = new BusinessCardTrackLog();
        if(isset($data["id"]) && !empty($data["id"])){
            $businessCardTrackLogModel = self::findOne($data["id"]);
            if(empty($businessCardTrackLogModel)){
                return false;
            }
            if($businessCardTrackLogModel->is_delete == self::YES){
                return false;
            }
            $data["mall_id"] = $businessCardTrackLogModel->mall_id;
        }
        if(isset($data["mall_id"])){
            $businessCardTrackLogModel->mall_id = $data["mall_id"];
        }
        if(isset($data["user_id"])){
            $businessCardTrackLogModel->user_id = $data["user_id"];
        }
        if(isset($data["track_type"])){
            $businessCardTrackLogModel->track_type = $data["track_type"];
        }
        if(isset($data["track_user_id"])){
            $businessCardTrackLogModel->track_user_id = $data["track_user_id"];
        }
        if(isset($data["remark"])){
            $businessCardTrackLogModel->remark = $data["remark"];
        }
        if(isset($data["ip"])){
            $businessCardTrackLogModel->ip = $data["ip"];
        }
        if(isset($data["model_id"])){
            $businessCardTrackLogModel->model_id = $data["model_id"];
        }
        if(isset($data["is_delete"])){
            $businessCardTrackLogModel->is_delete = $data["is_delete"];
        }
        $result = $businessCardTrackLogModel->save();
        return $result;
    }
}
