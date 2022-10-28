<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 用户
 * Author: zal
 * Date: 2020-04-15
 * Time: 18:45
 */

namespace app\forms\mall\user;

use app\core\ApiCode;
use app\forms\common\coupon\CouponListCommon;
use app\forms\mall\export\BalanceLogExport;
use app\forms\mall\export\ScoreExport;
use app\forms\mall\export\UserExport;
use app\models\BalanceLog;
use app\models\BaseModel;
use app\models\MemberLevel;
use app\models\Order;
use app\models\OrderRefund;
use app\models\ScoreLog;
use app\models\User;
use app\models\Distribution;
use app\models\UserCard;
use app\models\UserChildren;
use app\models\UserCoupon;
use app\models\UserInfo;
use app\models\UserRelationshipLink;
use app\plugins\boss\models\Boss;
use app\plugins\boss\models\BossAwardMember;
use app\plugins\boss\models\BossLevel;
use app\plugins\shopping_voucher\models\ShoppingVoucherUser;
use yii\helpers\ArrayHelper;

ini_set('memory_limit', '1024M');
class UserForm extends BaseModel
{
    public $id;
    public $member_level;
    public $role_type;
    public $page_size;
    public $keyword;
    public $kw_type;
    public $type;
    public $platform;
    public $user_id;
    public $status;
    public $date;
    public $start_date;
    public $end_date;
    public $is_admin;
    public $flag;
    public $fields;
    public $award_id;
    public $is_change_name = 0;
    public $search;
    public $is_lianc = 0;
    public $lock_parent = 0;

    public function rules()
    {
        return [
            [['date', 'flag',], 'trim'],
            [['start_date', 'end_date', 'keyword', 'platform','type'], 'string'],
            [['id', 'member_level', 'user_id', 'award_id', 'status', 'is_admin'], 'integer'],
            [['keyword'], 'string', 'max' => 255],
            [['page_size'], 'default', 'value' => 10],
            [['fields', 'is_lianc', 'lock_parent'], 'safe'],
            [['keyword', 'platform'], 'default', 'value' => ''],
            [['is_change_name'], 'boolean'],
            [['role_type', 'search', 'kw_type'], 'string']
        ];
    }

    public function distributionUser()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        };

        try {
            /** @var User $user */
            $user = User::find()->where(['id' => $this->user_id])->one();
            if (!$user) {
                throw new \Exception('用户不存在,ID:' . $this->user_id);
            }

            $query = User::find()->alias('u')
                ->where([
                    'AND',
                    ['u.mall_id' => \Yii::$app->mall->id],
                    ['u.is_inviter' => 1],
                    ['u.is_delete' => 0],
                    ['!=', 'u.id', $this->user_id],
                ]);

            if ($this->keyword) {
                $query->andWhere([
                    'or',
                    ['like', 'u.nickname', $this->keyword],
                    ['=', 'u.id', $this->keyword],
                ]);
            }
            $list = $query->select('u.id,u.nickname,u.username as name')->apiPage()->asArray()->all();
            foreach ($list as $k => $v) {
                $list[$k]['new_name'] = $v['nickname'];
                if ($v['name']) {
                    $list[$k]['new_name'] .= '/' . $v['name'];
                }
            }
            array_unshift($list, ['id' => 0, 'new_name' => '平台']);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function searchUser()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }


        $query = User::find()->alias('u')->select('u.id,u.nickname')->where([
            'AND',
            ['or', ['LIKE', 'u.nickname', $this->keyword], ['u.id' => $this->keyword], ['u.mobile' => $this->keyword]],
            ['u.mall_id' => \Yii::$app->mall->id],
            'u.mobile is not null'
        ]);
        $list = $query->orderBy('nickname')->limit(30)->all();

        $newList = [];
        /** @var User $item */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            //$newItem['avatar'] = $item->userInfo ? $item->userInfo->avatar_url : '';
            $platform = $item->userInfo ? $item->userInfo->platform : '';
            $newItem['nickname'] = User::getPlatformText($platform) . '（' . $item->nickname . '）';
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $newList
            ]
        ];
    }

    public function getCanBindInviter()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            //$child_ids = UserChildren::find()->where(['user_id' => $this->user_id, 'is_delete' => 0])->select('child_id')->column();

            $userRelationshipLink = UserRelationshipLink::findOne(["user_id" => $this->user_id]);
            if(!$userRelationshipLink){
                throw new \Exception("关系链异常");
            }
            $childSubQuery = UserRelationshipLink::find()->select(["user_id"])
                ->andWhere([
                    "AND",
                    [">", "left", $userRelationshipLink->left],
                    ["<", "right", $userRelationshipLink->right]
                ]);


            $query = User::find()->alias('u')->select('u.id,u.nickname')
                ->where(['u.is_inviter' => 1, 'u.mall_id' => \Yii::$app->mall->id])
                ->andWhere("u.mobile is not null")
                ->andWhere([
                    "OR",
                    ['LIKE', 'u.nickname', $this->keyword]
                ]);

            if(is_numeric($this->keyword)){
                $query->orWhere(
                    ['or', ['u.id' => $this->keyword], ['u.mobile' => $this->keyword]]
                );
            }

            $query->andWhere(['not in', 'u.id', $childSubQuery]);
            /*if (count($child_ids)) {
                $query->andWhere(['not in', 'u.id', $child_ids]);
            }*/
            $list = $query->orderBy('id desc')->limit(30)->all();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }


    //用户列表
    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $mall_id = \Yii::$app->mall->id;

        if(!empty($this->search)){
            $search = (array)@json_decode($this->search, true);
            foreach($search as $key => $val){
                if(isset($this->attributes[$key])){
                    $this->$key = $val;
                }
            }
        }

        $query = User::find()->alias('u')->where([
            'u.is_delete' => 0,
            'u.mall_id' => $mall_id
        ]);
        $query->andWhere(['and', ['!=', 'u.mobile', ''], ['IS NOT', 'u.mobile', NULL]]);
        $query->leftJoin(["p" => User::tableName()], "p.id=u.parent_id");
        $query->leftJoin(["url" => UserRelationshipLink::tableName()], "url.user_id=u.id");
        $query->leftJoin(["svu" => ShoppingVoucherUser::tableName()], "svu.user_id=u.id");
        $query->keyword($this->member_level, ['u.level' => $this->member_level]);
        if($this->platform){
            $query->leftJoin(['i' => UserInfo::tableName()], 'i.user_id = u.id');
            $query->keyword($this->platform, ['i.platform' => $this->platform]);
        }

        if ($this->keyword && $this->kw_type) {
            switch ($this->kw_type)
            {
                case "mobile":
                    $query->andWhere(['u.mobile' => $this->keyword]);
                    break;
                case "user_id":
                    $query->andWhere(['u.id' => $this->keyword]);
                    break;
                case "nickname":
                    $query->andWhere(['like', 'u.nickname', $this->keyword]);
                    break;
                default:
            }
        }elseif($this->keyword){
            $query->andWhere([
                "OR",
                ['u.mobile' => $this->keyword],
                ['u.id' => $this->keyword],
                ['like', 'u.nickname', $this->keyword]
            ]);
        }

        if(!empty($this->role_type)){
            $query->andWhere(["u.role_type" => $this->role_type]);
        }

        if($this->is_lianc){
            $query->andWhere(["u.is_lianc" => 1]);
        }

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new UserExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query);
            return false;
        }
        /*$cardQuery = UserCard::find()->where(['mall_id' => $mall_id, 'is_delete' => 0])
            ->andWhere('user_id = u.id')->select('count(1)');
        $couponQuery = UserCoupon::find()->where(['mall_id' => $mall_id, 'is_delete' => 0,'is_failure' => 0])
            ->andWhere('user_id = u.id')->select('count(1)');
        $orderQuery = Order::find()->where(['mall_id' => $mall_id, 'is_delete' => 0])
            ->andWhere('user_id = u.id')->select('count(1)');
        $orderSum = Order::find()->where(['mall_id' => $mall_id, 'is_delete' => 0, 'is_pay' => 1])
            ->andWhere('user_id = u.id')->select(['COALESCE(SUM(`total_price`),0)']);
        //未发货 成功取消的订单金额
        $orderSumCancel = Order::find()->where(['mall_id' => $mall_id, 'is_delete' => 0, 'is_pay' => 1, 'cancel_status' => 1])
            ->andWhere('user_id = u.id')->select(['COALESCE(SUM(`total_price`),0)']);
        //售后成功的订单金额
        $orderSumRefund = Order::find()->alias('o')->where(['o.mall_id' => $mall_id, 'o.is_delete' => 0, 'o.is_pay' => 1])
            ->leftJoin(['re' => OrderRefund::tableName()], 're.order_id=o.id')
            ->andWhere(['re.type' => 1, 're.status' => 2])
            ->andWhere('o.user_id = u.id')
            ->select(['COALESCE(SUM(`refund_price`),0)']);

        //用户下级数量
        $childSum = UserRelationshipLink::find()->alias("c_url")
            ->andWhere("c_url.left > url.left AND c_url.right < url.right")
            ->select('count(1)');*/

        $mall_members = MemberLevel::findAll(['mall_id' => $mall_id, 'status' => 1, 'is_delete' => 0]);
        $list = $query
            ->select(['u.id', 'u.role_type', 'u.static_integral', 'u.id as user_id', 'u.role_type', 'u.avatar_url', 'u.nickname', 'u.mobile', 'u.balance', 'u.level', 'u.score', 'u.static_score',
                'u.created_at', 'u.parent_id', 'p.nickname as parent_nickname', 'p.role_type as parent_role_type', 'p.mobile as parent_mobile', "COALESCE(svu.money,0) AS `shop_voucher_money`",
                '(u.income + u.income_frozen) as total_income', 'url.left as r_left', 'url.right as r_right',
                //'coupon_count' => $couponQuery,
                //'order_count' => $orderQuery,
                //'order_sum' => $orderSum,
                //'child_sum' => $childSum,
                //'order_sum_cancel' => $orderSumCancel,
                //'order_sum_refund' => $orderSumRefund,
                //'card_count' => $cardQuery
            ])
            ->page($pagination, $this->page_size)
            ->orderBy('u.id DESC')
            ->asArray()
            ->all();
        $roleTypes = [
            'store' => 'VIP代理商',
            'partner' => '区域服务商',
            'branch_office' => '城市服务商',
            'user' => '用户'
        ];
        foreach ($list as &$v) {

            $v['coupon_count'] = (int)UserCoupon::find()->where([
                'mall_id'    => $mall_id,
                'is_delete'  => 0,
                'is_failure' => 0,
                "user_id"    => $v['id']
            ])->count();
            //订单数量
            $v['order_count'] = (int)Order::find()->where([
                'mall_id'   => $mall_id,
                'is_delete' => 0,
                'user_id'   => $v['id'],
                'is_pay'    => 1
            ])->count();
            //订单金额
            $v['order_sum'] = (float)Order::find()->where([
                'mall_id'   => $mall_id,
                'is_delete' => 0,
                'is_pay'    => 1,
                'user_id'   => $v['id'],
            ])->sum("total_price");
            //团队人数
            $v['child_sum'] = (int)UserRelationshipLink::find()->alias("c_url")
                ->andWhere("c_url.left > '".$v['r_left']."' AND c_url.right < '".$v['r_right']."'")
                ->count();
            //取消的订单数
            $v['order_sum_cancel'] = (int)Order::find()->where([
                'mall_id'       => $mall_id,
                'is_delete'     => 0,
                'is_pay'        => 1,
                'cancel_status' => 1,
                'user_id'       => $v['id']
            ])->sum("total_price");
            //退款金额
            $v['order_sum_refund'] = (float)Order::find()->alias('o')->where(['o.mall_id' => $mall_id, 'o.is_delete' => 0, 'o.is_pay' => 1])
                ->leftJoin(['re' => OrderRefund::tableName()], 're.order_id=o.id')
                ->andWhere(['re.type' => 1, 're.status' => 2])
                ->andWhere(['o.user_id' => $v['id']])
                ->sum("refund_price");
            $v['card_count'] = (int)UserCard::find()->where([
                'mall_id'   => $mall_id,
                'is_delete' => 0,
                'user_id'   => $v['id']
            ])->count();
            if ($this->is_change_name) {
                $v['nickname'] = User::getPlatformText($v['platform']) . '（' . $v['nickname'] . '）';
            }
            $v['role_type_text'] = isset($roleTypes[$v['role_type']]) ? $roleTypes[$v['role_type']] : '';
            $v['order_sum'] = price_format($v['order_sum'] - $v['order_sum_cancel'] - $v['order_sum_refund']);

            $team_all = $this->getTeamOrder($v['user_id']);
            if (isset($team_all['order_num'])) {
                $v['team_order_sum'] = $team_all['order_num'];
                $v['team_order_price'] = $team_all['total_price'];
            } else {
                $v['team_order_sum'] = 0;
                $v['team_order_price'] = 0;
            }
        }
        unset($v);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
                'mall_members' => $mall_members,
                'exportList' => (new UserExport())->fieldsList(),
            ]
        ];
    }

    //用户编辑
    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        };
        /* @var User $user */
        $user = User::find()->alias('u')
            ->with('parent')
            ->where(['u.id' => $this->id, 'u.mall_id' => \Yii::$app->mall->id])
            ->one();

        if (!$user) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '数据为空',
            ];
        }

        $newList = [
            'id' => $user->id,
            'username' => $user->username,
            'nickname' => $user->nickname,
            'role_type' => $user->role_type,
            'role_type_label' => $user->role_type_label,
            'mobile' => $user->mobile,
            'avatar' => $user->avatar_url,
            'is_inviter'=>$user->is_inviter,
            'is_blacklist' => $user->is_blacklist,
            'parent_name' => $user->parent ? $user->parent->nickname : '平台',
            'money' => $user->balance ? $user->balance : 0,
            'member_level' => (int)$user->level,
            'created_at' => date("Y-m-d H:i:s", $user->created_at),
            'parent_id' => $user->parent_id,
            'is_examine' => $user->is_examine,
            'is_lianc' => (int)$user->is_lianc,
            'lock_parent' => (int)$user->lock_parent,
        ];

        $mall_members = MemberLevel::findAll(['mall_id' => \Yii::$app->mall->id, 'status' => 1, 'is_delete' => 0]);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'mall_members' => $mall_members,
            ]
        ];
    }

    /**
     * 优惠券信息
     * @return [type] [description]
     */
    public function getCoupon()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        };

        $form = new CouponListCommon();
        $form->status = $this->status;
        $form->user_id = $this->user_id;
        $form->date = $this->date;
        $data = $form->setExpired(false)->getUserCouponList();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $data
        ];
    }

    //优惠券删除
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $model = UserCoupon::findOne([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ]);

        if (!$model) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '数据不存在或已经删除',
            ];
        }
        $model->is_delete = 1;
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功'
        ];
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-20
     * @Time: 11:22
     * @Note:余额记录
     * @return array|bool
     */
    public function balanceLog()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        };

        $query = BalanceLog::find()->alias('b')->where([
            'b.mall_id' => \Yii::$app->mall->id,
        ])->joinwith(['user' => function ($query) {
            if ($this->keyword) {
                $query->where(['like', 'nickname', $this->keyword]);
            }
        }])->orderBy('id desc');

        if ($this->user_id) {
            $query->andWhere(['b.user_id' => $this->user_id]);
        }

        if ($this->start_date && $this->end_date) {
            $query->andWhere(['<', 'b.created_at', $this->end_date])
                ->andWhere(['>', 'b.created_at', $this->start_date]);
        }

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new BalanceLogExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query);
            return false;
        }

        $list = $query->page($pagination, $this->page_size)->asArray()->all();

        foreach ($list as &$v) {
            $v['info_desc'] = json_decode($v['custom_desc'], true);
        }
        unset($v);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'export_list' => (new BalanceLogExport())->fieldsList(),
                'pagination' => $pagination
            ]
        ];
    }

    /**
     * @Note:积分记录
     * @return array|bool
     */
    public function integralLog()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        };

        $query = ScoreLog::find()->alias('i')->where([
            'i.mall_id' => \Yii::$app->mall->id,
        ])->joinwith(['user' => function ($query) {
            if ($this->keyword) {
                $query->where(['like', 'nickname', $this->keyword]);
            }
        }])->orderBy('id desc');

        if ($this->user_id) {
            $query->andWhere(['i.user_id' => $this->user_id]);
        }

        if ($this->start_date && $this->end_date) {
            $query->andWhere(['<', 'i.created_at', $this->end_date])->andWhere(['>', 'i.created_at', $this->start_date]);
        }

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new ScoreExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query);
            return false;
        }

        $list = $query->page($pagination, $this->page_size)->asArray()->all();

        foreach ($list as &$v) {
            $v['info_desc'] = json_decode($v['custom_desc'], true);
        };
        unset($v);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'export_list' => (new ScoreExport())->fieldsList(),
                'pagination' => $pagination,
            ]
        ];
    }

    //获取股东用户
    public function getPlatformList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        if ($this->type == 'add') {
            $query = Boss::find()->alias('b')->where([
                'b.is_delete' => 0,
                'b.mall_id' => \Yii::$app->mall->id,
            ]);
            $query->leftJoin(['bl' => BossLevel::tableName()], 'b.level_id=bl.id');
            $select = "b.id,b.user_id,b.level_id,bl.name as level_name";
        } elseif ($this->type == 'show') {
            $query = BossAwardMember::find()->alias('b')->where([
                'b.award_id' => $this->award_id,
                'b.mall_id' => \Yii::$app->mall->id,
            ]);
            $select = "b.id,b.user_id";
        } else {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, '请传入参数');
        }

        $query->innerJoin(['u' => User::tableName()], 'u.id=b.user_id');

        $select .= ',u.id as uid,u.nickname';

        if ($this->keyword)
            $query->andWhere([
                'or',
                ['like', 'u.nickname', $this->keyword],
                ['u.id' => $this->keyword]
            ]);


        $list = $query->select($select)->page($pagination)
            ->orderBy('b.id DESC')
            ->asArray()
            ->all();

        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
            'list' => $list,
            'pagination' => $pagination
        ]);
    }

    //获取团队订单数、总金额
    public function getTeamOrder ($user_id)
    {
        try {
            $user_link = UserRelationshipLink::findOne(['user_id' => $user_id, 'is_delete' => 0]);
            if (!$user_link){
                throw new \Exception('用户关系链异常');
            }
            $user_ids = UserRelationshipLink::find()->alias('ul')
                    ->andWhere("ul.left > $user_link->left AND ul.right < $user_link->right")
                    ->select('user_id')->asArray()->all();
            $data = [
                'order_num' => 0,
                'total_price' => 0
            ];
            if ($user_ids && is_array($user_ids)) {
                $query = Order::find()->andWhere(['in','user_id',$user_ids]);
                $price_query = clone $query;
                //获取总订单数
                $data['order_num'] = $query->count();
                //获取总金额
                $data['total_price'] = $price_query->sum('total_pay_price');
            }
            return $data;
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}
