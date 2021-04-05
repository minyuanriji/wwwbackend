<?php

namespace app\models;

use app\core\cloud\Cloud;
use app\events\UserInfoEvent;
use app\handlers\RelationHandler;
use app\services\wechat\WechatTemplateService;
use Yii;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property int $id ID
 * @property string $username 用户名
 * @property string $password 密码
 * @property string $transaction_password 交易密码
 * @property string $auth_key
 * @property int $mall_id
 * @property int $mch_id
 * @property string $mobile
 * @property string $access_token
 * @property string $nickname
 * @property int $birthday
 * @property int $temp_parent_id 临时父级id
 * @property int $parent_id 第一父级ID（直推人id）
 * @property int $second_parent_id 第二推荐人id
 * @property int $third_parent_id 第三推荐人id
 * @property int $score 当前积分
 * @property string $avatar_url 头像
 * @property int $total_score 总积分
 * @property float $balance 当前余额
 * @property float $total_balance 总金额
 * @property float $total_income 总收益
 * @property float $income 已收益
 * @property float $income_frozen 冻结收益
 * @property int $junior_at 成为下级时间
 * @property int $is_delete 是否删除
 * @property int $is_blacklist 是否黑名单
 * @property int $created_at 创建时间
 * @property int $last_login_at 最后登录时间
 * @property string $login_ip 登录ip
 * @property int $level 会员等级
 * @property int $is_inviter 是否是邀请者
 * @property int $is_distributor 是否为分销商
 * @property int $inviter_at 推荐资格升级时间
 * @property int $source 用户来源
 * @property User $parent
 * @property User $secondParent
 * @property User $thirdParent
 * @property UserInfo $userInfo
 * @property User[] $firstChildren 一级下级
 * @property User[] $secondChildren 二级下级
 * @property User[] $thirdChildren 三级下级
 *
 *
 */
class User extends BaseActiveRecord implements \yii\web\IdentityInterface
{
    const EVENT_REGISTER = 'userRegister';
    const EVENT_LOGIN = 'userLogin';
    //微信小程序
    const PLATFORM_MP_WX = 'mp-wx';
    //支付宝小程序
    const PLATFORM_MP_ALI = 'mp-ali';
    //百度小程序
    const PLATFORM_MP_BD = 'mp-bd';
    //头条小程序
    const PLATFORM_MP_TT = 'mp-tt';
    //公众号
    const PLATFORM_WECHAT = 'wechat';
    //h5
    const PLATFORM_H5 = 'h5';

    /** @var int 是否有邀请资格 */
    const IS_INVITER_YES = 1;
    const IS_INVITER_NO = 0;

    /** @var string 用户来源1分享首页2分享海报3分享商品4分享内容5分享视频6分享资讯7分享名片 */
    const SOURCE_SHARE_INDEX = 1;
    const SOURCE_SHARE_POSTER = 2;
    const SOURCE_SHARE_GOODS = 3;
    const SOURCE_SHARE_CONTENT = 4;
    const SOURCE_SHARE_VIDEO = 5;
    const SOURCE_SHARE_NEWS = 6;
    const SOURCE_SHARE_CARD = 7;

    const UPGRADE_STATUS_CONDITION = 1;
    const UPGRADE_STATUS_GOODS = 2;

    public static $sources = [
        self::SOURCE_SHARE_INDEX => "分享首页",
        self::SOURCE_SHARE_POSTER => "分享海报",
        self::SOURCE_SHARE_GOODS => "分享商品",
        self::SOURCE_SHARE_CONTENT => "分享内容",
        self::SOURCE_SHARE_VIDEO => "分享视频",
        self::SOURCE_SHARE_NEWS => "分享资讯",
        self::SOURCE_SHARE_CARD => "分享名片",
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        // 'temp_parent_id', 
        return [
            [['username', 'password'], 'required'],
            [['mall_id', 'mch_id', 'parent_id', 'second_parent_id', 'third_parent_id', 'is_delete', 'is_blacklist',
                 'created_at', 'last_login_at', 'junior_at', 'level', 'birthday', 'is_inviter', 'inviter_at','source','upgrade_status'], 'integer'],
            [['password', 'auth_key', 'access_token', 'mobile', 'avatar_url', 'platform', 'login_ip', 'transaction_password'], 'string'],
            [['balance', 'total_balance', 'total_income', 'income','income_frozen','total_score','score'], 'number'],
            [['username'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {

        return [
            'id' => 'ID',
            'mall_id' => '商城id',
            'mch_id' => '商户id',
            'username' => '用户名',
            'password' => '密码',
            'transaction_password' => '交易密码',
            'mobile' => '手机号',
            'nickname' => '昵称',
            'birthday' => '生日',
            'access_token' => 'access_token',
            'auth_key' => 'auth_key',
            'avatar_url' => '头像',
            'platform' => '来源平台',
            'temp_parent_id' => '临时推荐人id',
            'parent_id' => '父级id(直推人id)',
            'second_parent_id' => '第二推荐人id',
            'third_parent_id' => '第三推荐人id',
            'score' => '积分',
            'total_score' => '总积分',
            'balance' => '余额',
            'total_balance' => '总金额',
            'total_income' => '总佣金',
            'income' => '佣金',
            'income_frozen' => '冻结佣金',
            'junior_at' => '加入下级时间',
            'created_at' => '创建时间',
            'last_login_at' => '最后登录时间',
            'login_ip' => '登录ip',
            'is_delete' => '是否删除',
            'is_blacklist' => '是否黑名单',
            'is_distributor' => '是否为分销商',
            'level' => '会员等级',
            'is_inviter' => '邀请者',
            'inviter_at' => '推荐资格升级时间',
            'source' => '来源'
        ];
    }

    /**
     * 根据给到的ID查询身份。
     * @Author: 广东七件事 zal
     * @Date: 2020-04-08
     * @Time: 19:49
     * @param string|integer $id 被查询的ID
     * @return User|null 通过ID匹配到的身份对象
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * 根据 token 查询身份。
     * @Author: 广东七件事 zal
     * @Date: 2020-04-15
     * @Time: 14:33
     * @param string $token 被查询的 token
     * @param int $type
     * @return User|null 通过 token 得到的身份对象
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-04-15
     * @Time: 14:49
     * @return int|string 当前用户ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-04-15
     * @Time: 11:49
     * @return string 当前用户的（cookie）认证密钥
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-04-15
     * @Time: 11:49
     * @param string $authKey
     * @return boolean if auth key is valid for current user
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * 通过用户名查找用户
     * @Author: 广东七件事 zal
     * @Date: 2020-04-15
     * @Time: 11:49
     * @param string $username
     * @return User|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * 获取平台来源名称
     * @param $platform
     * @return string
     */
    public static function getPlatformText($platform)
    {
        switch ($platform) {
            case self::PLATFORM_MP_WX:
                $text = '微信';
                break;
            case self::PLATFORM_WECHAT:
                $text = '微信公众号';
                break;
            case self::PLATFORM_MP_ALI:
                $text = '支付宝';
                break;
            case self::PLATFORM_MP_BD:
                $text = '百度';
                break;
            case self::PLATFORM_MP_TT:
                $text = '抖单/头条';
                break;
            default:
                $text = '未知';
                break;
        }

        return $text;
    }

    public static function getOneUser($where)
    {
        return User::find()->where($where)->one();
    }

    public function getStore()
    {
        return $this->hasOne(Store::className(), ['id' => 'store_id'])
            ->viaTable(User::tableName(), ['id' => 'id', 'is_delete' => 'is_delete']);
    }

    public function getDistribution()
    {
        return $this->hasOne(\app\plugins\distribution\models\Distribution::className(), ['user_id' => 'id','is_delete' => 0]);
    }

    public function getParent()
    {
        return $this->hasOne(User::className(), ['id' => 'parent_id']);
    }

    public function getSecondParent()
    {
        return $this->hasOne(User::className(), ['id' => 'second_parent_id']);
    }

    public function getThirdParent()
    {
        return $this->hasOne(User::className(), ['id' => 'third_parent_id']);
    }

    public function getFormId()
    {
        return $this->hasMany(Formid::className(), ['user_id' => 'id']);
    }

    public function getOneFormId()
    {
        return $this->hasOne(Formid::className(), ['user_id' => 'id']);
    }

    public function setDistribution($val)
    {
        $this->distribution = $val;
    }

    public function getMall()
    {
        return $this->hasMany(Mall::className(), ['user_id' => 'id']);
    }

    public function getFirstChildren()
    {
        return $this->hasMany(self::className(), ['parent_id' => 'user_id']);
    }

    public function getSecondChildren()
    {
        return $this->hasMany(self::className(), ['parent_id' => 'user_id'])
            ->via('firstChildren');
    }

    public function getThirdChildren()
    {
        return $this->hasMany(self::className(), ['parent_id' => 'user_id'])
            ->via('secondChildren');
    }

    public function getUserInfo()
    {
        return $this->hasOne(UserInfo::className(), ['user_id' => 'id']);
    }

    public function getUserSetting()
    {
        return $this->hasOne(UserSetting::className(), ['user_id' => 'id']);
    }


    //设置为邀请者
    public function setInviter()
    {
        \Yii::warning("---setInviter start---");
        if (!$this->is_inviter) {
            $this->is_inviter = 1;
            $this->inviter_at = time();
            $this->save();
            \Yii::$app->trigger(RelationHandler::INVITER_STATUS_CHANGE, new UserInfoEvent([
                'user_id' => $this->id,
                'mall_id' => $this->mall_id,
                'is_inviter' => $this->is_inviter
            ]));
            \Yii::warning('设置用户为邀请者');
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-21
     * @Time: 15:57
     * @Note:绑定用户
     * @param $before_parent_id
     * @return bool
     */
    public function bindParent($before_parent_id)
    {

        \Yii::warning('绑定上级');

        \Yii::$app->trigger(RelationHandler::CHANGE_PARENT, new UserInfoEvent([
            'user_id' => $this->id,
            'mall_id' => $this->mall_id,
            'parent_id' => $this->parent_id,
        ]));
        if ($this->save()) {
            //变更记录记录
            $log = new ParentLog();
            $log->user_id = $this->id;
            $log->before_parent_id = $before_parent_id;
            $log->after_parent_id = $this->parent_id;
            $log->mall_id = $this->mall_id;
            $log->save();
            \Yii::warning($log->getErrors());

            $this->sendWechatTemp($this->id);
            //发送模板消息

            return true;
        } else {
            return false;
        }
    }

    public function sendWechatTemp($user_id)
    {
        $user = User::findOne($user_id);

        if (!$user) {
            return false;
        }

        if ($user->parent_id == 0) {
            return false;
        }

        $WechatTemplateService = new WechatTemplateService($user->mall_id);

        $url = "/plugins/extensions/index";

        $h5_url = \Yii::$app->params['web_url'] . "/h5/#" . $url;

        $platform = $WechatTemplateService->getPlatForm();

        $send_data = [
            'first'    => '下线代理加入通知',
            'keyword1' => $user->id,
            'keyword2' => date('Y-m-d H:i:s', time()),
            'keyword3' => $user->nickname,
        ];

        return $WechatTemplateService->send($user->parent_id, WechatTemplateService::TEM_KEY['userRegister']['tem_key'], $h5_url, $send_data, $platform, $url);
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-21
     * @Time: 15:39
     * @Note:获取所有上级
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getParentList()
    {
        $parent_list = UserParent::find()->where(['user_id' => $this->id, 'is_delete' => 0, 'mall_id' => $this->mall_id])->all();
        return count($parent_list)>0 ? $parent_list : [];
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-21
     * @Time: 15:39
     * @Note:获取所有下级
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getChildList()
    {
        $child_list = UserChildren::find()->where(['user_id' => $this->id, 'is_delete' => 0, 'mall_id' => $this->mall_id])->all();
        return count($child_list) ? $child_list : [];
    }

    /**
     * 通过不同条件更新用户
     * @param array $condition 要更新的字段数据
     * @param array $fields 要更新的字段名
     * @return int
     */
    public static function updateUserByCondition($condition,$fields){
        return User::updateAll($condition,$fields);
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
        if(isset($params["user_type"]) && !empty($params["user_type"])){
            $query->andWhere(["user_type" => $params["user_type"]]);
        }
        if(isset($params["parent_id"]) && !empty($params["operate_id"])){
            $query->andWhere(["operate_id" => $params["operate_id"]]);
        }
        if(isset($params["status"]) && !empty($params["status"])){
            $query->andWhere(["status" => $params["status"]]);
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
     * 获取用户可用购物券总额
     * @Author bing
     * @DateTime 2020-10-08 17:10:02
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $user_id
     * @return void
     */
    public static function getCanUseIntegral($user_id){
        $user = self::find()->where(array('id'=>$user_id))->one();
        return empty($user) ? 0 : ($user['static_integral'] + $user['dynamic_integral']);
    }

    /**
     * 获取用户可用购物券总额
     * @Author bing
     * @DateTime 2020-10-08 17:10:02
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $user_id
     * @return void
     */
    public static function getCanUseScore($user_id){
        $user = self::find()->where(array('id'=>$user_id))->one();
        return empty($user) ? 0 : ($user['static_score'] + $user['dynamic_score']);
    }

    /**
     * 获取用户钱包
     * @Author bing
     * @DateTime 2020-10-10 11:29:11
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $user_id
     * @return object|array|boolean
     */
    public static function getUserWallet($user_id, $mall_id = null){
        $selects = 'id,mall_id,parent_id,second_parent_id,third_parent_id,is_inviter,static_integral,dynamic_integral,score,static_score,dynamic_score';
        $wallet = self::find()->select($selects)
                    ->where([
                        'id'      =>$user_id,
                        'mall_id' => Yii::$app->mall->id ?? $mall_id
                    ])->one();
        $wallet['dynamic_integral'] = $wallet['score'];
        return $wallet;
    }

    /**
     * 更新用户钱包
     * @return boolean
     */
    public static function updateUserWallet(User $user){

        //查询用户可用积分券按过期时间升序排列
        $can_use_integrals = IntegralRecord::getIntegralAscExpireTime($user->id, 0);
        $dynamicScores = 0;

        foreach($can_use_integrals as $integral){
            $dynamicScores += !empty($integral['deduct']) ? $integral['money'] + array_sum(array_column($integral['deduct'], 'money')) : $integral['money'];
        }

        $user->score         = $dynamicScores;
        $user->dynamic_score = $user->score;
        $user->total_score   = $user->dynamic_score + $user->static_score;

        return $user->save();
    }


    public static function getOneUserFlag($user_id,$mall_id=null){
        return self::find()
            ->select('id')
            ->where(array('id'=>$user_id,'mall_id'=>Yii::$app->mall->id ?? $mall_id)) -> asArray()
            ->one();
    }

}
