<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 优惠券
 * Author: zal
 * Date: 2020-04-18
 * Time: 14:21
 */

namespace app\forms\mall\statistics;

use app\component\jobs\CouponExpireJob;
use app\core\ApiCode;
use app\forms\common\coupon\CouponCommon;
use app\forms\common\goods\GoodsCatsCommon;
use app\forms\common\template\tplmsg\ActivitySuccessTemplate;
use app\logic\CatsLogic;
use app\logic\UserLogic;
use app\models\BaseModel;
use app\models\Coupon;
use app\models\CouponCatRelation;
use app\models\CouponGoodsRelation;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\MemberLevel;
use app\models\User;
use app\models\CouponCenter;
use app\models\CouponMemberRelation;
use app\forms\common\coupon\CouponMallRelation;

class CouponForm extends BaseModel
{
    public $keyword;
    public $page;
    public $page_size;
    public $user_id_list;
    public $cat_id_list;
    public $goods_id_list;
    public $couponMember;
    public $id;
    public $mall_id;
    public $name;
    public $type;
    public $discount;
    public $discount_limit;
    public $min_price;
    public $sub_price;
    public $expire_type;
    public $expire_day;
    public $begin_at;
    public $end_at;
    public $total_count;
    public $is_join;
    public $sort;
    public $rule;
    public $pic_url;
    public $desc;
    public $is_member;
    public $appoint_type;
    public $coupon_num;
    public $is_send;

    public function rules()
    {
        return [
            [['couponMember'], 'trim'],
            [['id', 'mall_id', 'type', 'pic_url', 'total_count', 'is_join', 'sort', 'expire_type', 'expire_day',
                'appoint_type', 'is_member', 'coupon_num'], 'integer'],
            [['min_price', 'sub_price', 'discount_limit'], 'number', 'min' => 0, 'max' => 999999999],
            [['begin_at', 'end_at', 'user_id_list', 'cat_id_list', 'goods_id_list'], 'safe'],
            [['name', 'keyword'], 'string', 'max' => 255],
            [['desc', 'rule'], 'string', 'max' => 2000],
            [['expire_day'], 'integer', 'min' => 0, 'max' => 999],
            [['discount',], 'number'],
            [['sort'], 'integer', 'min' => 0, 'max' => 999999999],
            [['page'], 'default', 'value' => 1],
            [['coupon_num', 'is_send'], 'default', 'value' => 0],
            [['page_size', 'pic_url'], 'default', 'value' => 10],
            [['begin_at', 'end_at'], 'default', 'value' => '0000-00-00 00:00:00'],
            [['rule', 'desc', 'keyword'], 'default', 'value' => ''],
        ];
    }


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id_list' => '发放对象',
            'mall_id' => 'mall ID',
            'name' => '优惠券名称',
            'type' => '优惠券类型：1=折扣，2=满减',
            'discount' => '折扣率',
            'discount_limit' => '优惠上限',
            'pic_url' => '未用',
            'desc' => '未用',
            'min_price' => '最低消费金额',
            'sub_price' => '优惠金额',
            'total_count' => '发放总数量',
            'is_join' => '是否加入领券中心',
            'sort' => '排序按升序排列',
            'expire_type' => '到期类型',
            'expire_day' => '有效天数',
            'begin_at' => '有效期开始时间',
            'end_at' => '有效期结束时间',
            'rule' => '使用说明',
            'is_member' => '是否指定会员等级领取',
            'is_delete' => '删除',
            'appoint_type' => '指定类型',
        ];
    }

    /**
     * 获取列表数据
     * @return array
     */
    public function getList()
    {

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $query = Coupon::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);

        $data = $query->with(['couponCenter' => function ($query) {
            $query->where(['is_delete' => 0]);
        }])->keyword($this->keyword, ['like', 'name', $this->keyword])
            ->page($pagination)
            ->orderBy('id DESC')
            ->asArray()
            ->all();
        foreach ($data as $k => $v) {
            $form = new CouponCommon();
            $form->user = false;
            $data[$k]['count'] = $v['total_count'] + $form->getCount($v['id']);
            $data[$k]['is_join'] = $v['couponCenter'] ? '1' : '0';
        };

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $data,
                'pagination' => $pagination
            ]
        ];
    }

    /**
     * 删除
     * @return array
     */
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $model = Coupon::findOne([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '数据不存在或已删除',
            ];
        }
        $model->is_delete = 1;
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => 'success'
        ];
    }

    /**
     * 详情
     * @return array
     */
    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $coupons = Coupon::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
        ])
            ->with(['cat', 'goodsWarehouse'])
            ->with(['couponMember' => function ($query) {
                $query->where(['is_delete' => 0]);
            }])
            ->with(['couponCenter' => function ($query) {
                $query->where(['is_delete' => 0]);
            }])
            ->asArray()
            ->one();
        if ($coupons) {
            $coupons['is_member'] = $coupons ? (int)$coupons['is_member'] : 0;
            $coupons['goods_id_list'] = $coupons['cat_id_list'] = [];
            $coupons['is_join'] = isset($coupons['couponCenter']) ? 1 : 0;
            $coupons['total_count'] = isset($coupons['total_count']) ? intval($coupons['total_count']) : 0;

            if (array_key_exists('cat', $coupons) && $coupons['cat']) {
                $array = [];
                foreach ($coupons['cat'] as $v) {
                    $array[] = (int)$v['id'];
                }
                $coupons['cat_id_list'] = $array;
            }

            if (array_key_exists('goodsWarehouse', $coupons) && $coupons['goodsWarehouse']) {
                $array = [];
                foreach ($coupons['goodsWarehouse'] as $v) {
                    $array[] = (int)$v['id'];
                }
                $coupons['goods_id_list'] = $array;
            }
            if (array_key_exists('couponMember', $coupons) && $coupons['couponMember']) {
                $coupons['couponMember'] = array_column($coupons['couponMember'], 'member_level');
            }

            if ($coupons['expire_type'] == 2) {
                if ($coupons['begin_at']) {
                    $coupons['begin_at'] = date('Y-m-d H:i:s', $coupons['begin_at']);
                    $coupons['end_at'] = date('Y-m-d H:i:s', $coupons['end_at']);
                }
            }
        }
        $members = MemberLevel::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ])->asArray()->all();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $coupons,
                'members' => $members,
                'cats' => CatsLogic::getAllCats(),
            ]
        ];
    }

    /**
     * 切换领劵中心
     * @return array
     */
    public function editCenter()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        //领劵中心
        $couponCenter = CouponCenter::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'coupon_id' => $this->id,
            'is_delete' => 0,
        ]);

        if ($this->is_join) {
            if (!$couponCenter) {
                $form = new CouponCenter();
                $form->mall_id = \Yii::$app->mall->id;
                $form->coupon_id = $this->id;
                $form->save();
            }
        } else {
            if ($couponCenter) {
                $couponCenter->is_delete = 1;
                $couponCenter->save();
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '切换成功'
        ];
    }

    /**
     * 保存
     * @return array
     * @throws \yii\db\Exception
     */
    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $model = Coupon::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
        ]);
        if (!$model) {
            $model = new Coupon();
        }


        $t = \Yii::$app->db->beginTransaction();
        if ($this->appoint_type == 1 && !$this->cat_id_list) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '类别不能为空'
            ];
        }

        if ($this->expire_type == 2) {
            if (($this->begin_at == '0' || $this->end_at == '0')) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '时间不能为空'
                ];
            } else if (strtotime(date("Y-m-d 23:59:59", strtotime($this->end_at))) < time()) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '结束时间必须大于当前时间'
                ];
            }
        }

        if ($this->appoint_type == 2 && !$this->goods_id_list) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '商品不能为空'
            ];
        }

        if ($this->type == 1) {
            if ($this->discount < 0.1 || $this->discount > 0.99) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '折扣范围0.1-0.99'
                ];

            }
        }

        if ($this->type == 1 && !empty($this->discount_limit) && $this->discount_limit <= 0) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '优惠上限必须大于0'
            ];
        }

        if ($this->type == 2) {
            $this->discount_limit = 0;
            $this->discount = 0;
        }
        $begin_at = strtotime(date("Y-m-d 00:00:00", strtotime($this->begin_at)));
        $end_at = strtotime(date("Y-m-d 23:59:59", strtotime($this->end_at)));
        if ($this->expire_type == 1) {
            $begin_at = time();
            $end_at = strtotime("+{$this->expire_day} day");
        }
        $this->begin_at = $begin_at;
        $this->end_at = $end_at;
        $model->attributes = $this->attributes;
        $model->mall_id = \Yii::$app->mall->id;
        if ($model->save()) {
            //领劵中心
            $couponCenter = CouponCenter::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'coupon_id' => $model->id,
                'is_delete' => 0,
            ]);

            if ($this->is_join) {
                if (!$couponCenter) {
                    $form = new CouponCenter();
                    $form->mall_id = \Yii::$app->mall->id;
                    $form->coupon_id = $model->id;
                    $form->save();
                }
            } else {
                if ($couponCenter) {
                    $couponCenter->is_delete = 1;
                    $couponCenter->save();
                }
            }

            //会员优惠券
            $array = [];
            CouponMemberRelation::updateAll(['is_delete' => 1, 'deleted_at' => time()], [
                'mall_id' => \Yii::$app->mall->id,
                'coupon_id' => $model->id,
                'is_delete' => 0,
            ]);
            $this->couponMember = $this->couponMember ?: [];
            foreach ($this->couponMember as $v) {
                $array[] = [
                    \Yii::$app->mall->id, $model->id, $v, 0, time(), '0',
                ];
            }
            if (isset($array)) {
                \Yii::$app->db->createCommand()
                    ->batchInsert(
                        CouponMemberRelation::tableName(),
                        ['mall_id', 'coupon_id', 'member_level', 'is_delete', 'created_at', 'deleted_at'],
                        $array
                    )
                    ->execute();
            }
            //指定分类或商品
            if ($this->appoint_type == 1) {
                CouponCatRelation::updateAll(['is_delete' => 1], ['coupon_id' => $this->id]);
                foreach ($this->cat_id_list as $id) {
                    $form = new CouponCatRelation();
                    $form->coupon_id = $model->id;
                    $form->cat_id = $id;
                    $form->is_delete = 0;
                    $form->save();
                };
            } elseif ($this->appoint_type == 2) {
                CouponGoodsRelation::updateAll(['is_delete' => 1], ['coupon_id' => $this->id]);
                foreach ($this->goods_id_list as $id) {
                    $form = new CouponGoodsRelation();
                    $form->coupon_id = $model->id;
                    $form->goods_warehouse_id = $id;
                    $form->is_delete = 0;
                    $form->save();
                };
            }
            if ($end_at > time()) {
                $expire = $end_at - time();
                \Yii::warning("couponForm save expire={$expire},coupon_id=" . $model->id);
                $dataArr = [
                    'id' => $model->id,
                ];
                $class = new CouponExpireJob($dataArr);
                \Yii::$app->queue->delay($expire)->push($class);
            }
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            $t->rollBack();
            return $this->responseErrorInfo($model);
        }
    }

    /**
     * 发送
     * @return array
     */
    public function send()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $model = Coupon::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id,
                'is_delete' => 0,
            ]);

            if (!$model) {
                throw new \Exception('优惠券不存在');
            }

            if ($this->coupon_num == 0) {
                throw new \Exception('发送数量不能为空');
            }

            if ($model->total_count < $this->coupon_num && $model->total_count != -1) {
                throw new \Exception('优惠券数量不足');
            }

            $userList = User::find()->where([
                'id' => $this->user_id_list,
                'mall_id' => \Yii::$app->mall->id
            ])->all();

            $count = 0;
            $num = 0;
            $msg = null;
            foreach ($userList as $u) {
                try {
                    $common = new CouponCommon(['coupon_id' => $this->id], false);
                    $coupon = $common->getAutoDetail();
                    $common->user = $u;
                    $class = new CouponMallRelation($coupon);

                    for ($i = 0; $i < $this->coupon_num; $i++) {
                        $msg = "操作完成，";
                        if (!$common->receive($coupon, $class, '后台发放')) {
                            $msg = "优惠券数量不够，";
                            break;
                        }
                        $num++;
                    }
                    $count++;
                } catch (\Exception $e) {
                    dd($e);
                }

                //是否发送模版消息，——发送开关
                if ($this->is_send) {
                    $tplMsg = new ActivitySuccessTemplate([
                        'page' => 'pages/coupon/index/index',
                        'user' => $u,
                        'activityName' => '优惠券发放',
                        'name' => $coupon->name,
                        'remark' => '您有新的优惠券待查收'
                    ]);
                    $tplMsg->send();
                }
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $msg . "共发放{$count}人次,{$num}张。",
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }

    /**
     * 搜索用户
     * @return array
     */
    public function searchUser()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => UserLogic::searchUser($this->keyword)
        ];
    }

    /**
     * 分类搜索
     * @return array
     */
    public function searchCat()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => GoodsCatsCommon::searchCat($this->keyword)
        ];
    }

    /**
     * 商品搜索
     * @return array
     */
    public function searchGoods()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $goodsId = Goods::find()->select('goods_warehouse_id')
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'sign' => '', 'mch_id' => 0]);

        $list = GoodsWarehouse::find()
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $goodsId])
            ->page($pagination, 20, $this->page)
            ->keyword($this->keyword !== '', ['like', 'name', $this->keyword])
            ->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
            ]
        ];
    }

    /**
     * 获取优惠券列表
     * @return array
     */
    public function getOptions()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $list = Coupon::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])->andWhere([
            'or',
            [
                'and',
                ['expire_type' => 2],
                ['>', 'end_at', time()]
            ],
            ['expire_type' => 1]
        ])
            ->page($pagination)
            ->orderBy('id DESC')
            ->asArray()
            ->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }
}
