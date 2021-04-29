<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单优惠卷处理
 * Author: xuyaoxiang
 * Date: 2020/9/15
 * Time: 11:42
 */

namespace app\services\Order;

use app\logic\CouponLogic;
use app\models\UserCoupon;

class CouponService
{
    private $item;
    private $user_coupon_list;
    private $form_data;
    //已经选择商品优惠卷列表
    private $use_coupon_list;
    private $use_coupon_list_array = [];
    private $use_user_coupon_ids = [];
    private $enableCoupon;
    public function __construct($item, $type,$enableCoupon)
    {
        $this->item         = $item;
        $this->type         = $type;
        $this->enableCoupon = $enableCoupon;
    }

    /**
     * 表单数据
     * @param $from_data
     */
    public function setFormData($from_data)
    {
        $this->form_data = $from_data;
        if (!empty($from_data['use_coupon_list'])) {
            $this->use_coupon_list = $from_data['use_coupon_list'];
            foreach ($this->use_coupon_list as $value) {
                $this->use_coupon_list_array[$value['user_coupon_id']] = $value['goods_id'];
                array_push($this->use_user_coupon_ids, $value['user_coupon_id']);
            }
        }
    }

    /**
     * 获取用户所有可用优惠券列表
     */
    public function getUserCounponList()
    {
        $this->user_coupon_list = UserCoupon::find()->where([
            'mall_id'    => \Yii::$app->mall->id,
            'user_id'    => \Yii::$app->user->identity->id,
            'is_use'     => 0,
            'is_failure' => 0,
            'is_delete'  => 0
        ])
            ->andWhere([
                ">=",
                "end_at",
                time()
            ])->with(["coupon"])->asArray()->all();

        foreach ($this->user_coupon_list as &$coupon) {
            if ($coupon['begin_at']) {
                $coupon['begin_at'] = date('Y-m-d', $coupon['begin_at']);
                $coupon['end_at']   = date('Y-m-d', $coupon['end_at']);
            }
            if (isset($coupon["coupon"])) {
                $coupon["coupon"]['begin_at'] = date('Y-m-d', $coupon["coupon"]['begin_at']);
                $coupon["coupon"]['end_at']   = date('Y-m-d', $coupon["coupon"]['end_at']);
            }
            if (!empty($coupon["coupon_data"])) {
                $coupon["coupon_data"]             = json_decode($coupon["coupon_data"], true);
                $coupon["coupon_data"]['begin_at'] = date('Y-m-d', $coupon["coupon_data"]['begin_at']);
                $coupon["coupon_data"]['end_at']   = date('Y-m-d', $coupon["coupon_data"]['end_at']);
            }
        }

        return $this->user_coupon_list;
    }

    //获取当前商品用户可用优惠卷列表
    public function getUserGoodsCouponList()
    {
        //优惠卷功能是否开启
        if (!$this->enableCoupon) {
            return $this->item;
        }

        //获取用户优惠卷列表,如果没有可用优惠券,直接返回数据
        $this->getUserCounponList();
        if (!$this->user_coupon_list) {
            return $this->item;
        }

        foreach ($this->item['same_goods_list'] as &$goodsItem) {
            foreach ($this->user_coupon_list as $coupon) {
                //判断该商品是否可以使用用户的优惠卷
                $data = CouponLogic::getUserUsableCoupon($coupon, $coupon["coupon"], $goodsItem);
                if (!empty($data)) {
                    //判断该优惠卷是否已经被选中
                    if (in_array($data['id'], $this->use_user_coupon_ids)) {
                        //如果选择，只在选中对应的商品优惠卷列表显示, 其他商品不显示
                        if ($goodsItem['goods_id'] == $this->use_coupon_list_array[$data['id']]) {
                            array_push($goodsItem['coupon_list'], $data);
                        }
                    } else {
                        array_push($goodsItem['coupon_list'], $data);
                    }
                }
            }
        }

        return $this->item;
    }

    /**
     * 检查是否合法优惠卷,匹配商品和用户
     */
    public function getUsableUserCouponId()
    {
        //用户没有使用优惠券，不需要进一步验证
        if (!$this->use_coupon_list) {
            return $this->item;
        }

        foreach ($this->use_coupon_list as $value) {
            if (!isset($this->item['same_goods_list'][$value['goods_id']])) {
                continue;
            }
            if(!isset($this->item['same_goods_list'][$value['goods_id']]['coupon_list'])){
                continue;
            }
            $coupon_list = array_column($this->item['same_goods_list'][$value['goods_id']]['coupon_list'], 'id');
            if (in_array($value['user_coupon_id'], $coupon_list)) {
                $this->item['same_goods_list'][$value['goods_id']]['usable_user_coupon_id'] = $value['user_coupon_id'];
            } else {
                $this->item['same_goods_list'][$value['goods_id']]['usable_user_coupon_id'] = 0;
            }
        }

        return $this->item;
    }
}