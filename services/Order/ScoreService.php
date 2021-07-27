<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/9/14
 * Time: 9:41
 */

namespace app\services\Order;

use app\helpers\ArrayHelper;
use app\logic\AppConfigLogic;
use app\models\IntegralRecord;
use app\models\User;

class ScoreService
{
    public $item;
    private $enable_score = false;
    private $user_score; //用户积分
    private $user_use_score = 0; //用户已使用积分
    private $user_remaining_score; //用户剩余积分
    private $score_status; //后台积分开启状态
    private $score_price; //多少积分抵扣一元;积分比例
    private $score = []; //返回积分字段
    private $use_score; //用户是否使用积分
    private $type; //订单预览还是订单提交
    private $total_goods_price;//订单的商品总价

    public function __construct($item, $type, $use_score, $enable_score)
    {
        $this->item              = $item;
        $this->total_goods_price = $item['total_goods_price'];
        $this->type              = $type;
        $this->use_score         = $use_score;
        $this->enable_score      = $enable_score;
        $this->getPaymentConfig();
        $this->getUserScore();
        $this->setForeheadScore();
        $this->getItemScore();
    }

    /**
     * 积分比例
     * @return mixed
     */
    public function getScorePrice()
    {
        return $this->score_price;
    }

    /**
     * 用户剩余积分
     * @return int
     */
    private function getUserRemainingScore()
    {
        $this->user_remaining_score = $this->user_score - $this->user_use_score;

        return $this->user_remaining_score;
    }

    /**
     * 获取用户初始积分可抵扣的最大金额
     * @return float|int
     */
    private function getMaxUserScorePrice()
    {
        if ($this->user_score <= 0) {
            return 0;
        }
        return $this->user_score * $this->score_price;
    }

    /**
     * 获取可抵扣最大金额
     * @param $forehead_score
     * @return mixed
     */
    private function getMaxDeductionPrice($forehead_score)
    {
        return min($this->getMaxUserScorePrice(), $forehead_score);
    }

    //计算实际积分抵扣最大值
    /*
     * 单件以规格单价高的为准
     * 累计的以商品总价为准
     * 不超过订单总价
     */
    private function setForeheadScore()
    {
        foreach ($this->item['same_goods_list'] as $key => $value) {
            $this->item['same_goods_list'][$key]['use_num']         = 0;
            $this->item['same_goods_list'][$key]['deduction_price'] = 0;

            if($value['forehead_score']==0){
                continue;
            }
            if ($value['forehead_score_type'] == 1) {
                if ($value['accumulative'] == 0) {
                    $this->item['same_goods_list'][$key]['forehead_score'] = min($value['forehead_score'],$value['max_goods_attr_price']);
                } else {
                    $value = $this->getAccumulative($value);
                    $this->item['same_goods_list'][$key]['forehead_score'] = min($value['accumulative_forehead_score'],$value['total_price']);
                }
            }
        }
    }

    private function getAccumulative($value)
    {
        $value['accumulative_forehead_score'] = $value['num'] * $value['forehead_score'];

        return $value;
    }

    private function getScore($price)
    {
        if ($this->score_price > 0) {
            return price_format($price / $this->score_price);
        }
        return 0;
    }

    public function countScore()
    {
        if(!$this->use_score){
            return $this->item;
        }

        if (!$this->score_status) {
            return $this->item;
        }

        foreach ($this->item['same_goods_list'] as $key => $value) {

            //当用户剩余积分等于,跳过
            if (($this->getUserRemainingScore() == 0) or $this->total_goods_price == 0) {
                continue;
            }

            $value['forehead_score'] = min($value['forehead_score'], $this->user_remaining_score, $this->item['total_goods_price'], $value['total_price']);
//            $value['forehead_score'] = intval($value['forehead_score']);

            if ($value['forehead_score_type'] == 1) {
                if ($value['accumulative'] == 0) {
                    $this->item['same_goods_list'][$key]['max_goods_attr_price'] -= $value['forehead_score'];
                }

                $this->item['same_goods_list'][$key]['total_price'] -= $value['forehead_score'];
            }

            $this->user_use_score       += $this->getScore($value['forehead_score']);
            $this->user_remaining_score -= $this->getScore($value['forehead_score']);

            //当前商品可抵扣积分，和可抵扣金额
            $this->item['same_goods_list'][$key]['deduction_price'] = $value['forehead_score'];
            $this->item['same_goods_list'][$key]['use_num']         = $this->getScore($value['forehead_score']);

            $this->total_goods_price -= $value['forehead_score'];

            //计算优惠比例
            foreach ($value['goods_list'] as $goods_list_key => $goods_item) {
                $goods                    = &$this->item['same_goods_list'][$key]['goods_list'][$goods_list_key];
                $goods['total_price']     = SameGoodsService::countAttrGoodsList($goods_item['total_price'], $goods_item['total_price_percent'], $value['forehead_score']);

                $goods['score_price']     = price_format($goods_item['total_price_percent']* $value['forehead_score']);

                $goods['use_score_price'] = intval($goods_item['total_price_percent']* $this->getScore($value['forehead_score']));

                $goods['use_score']       = $goods['use_score_price'] > 0 ? 1 : 0;
            }
        }

        $this->getItemScore();

        $this->item['total_goods_price'] = $this->total_goods_price;

        return $this->item;
    }

    public function getUserScore()
    {
        /** @var User $user */
        $user = \Yii::$app->user->identity;

        //先更新用户钱包
        User::updateUserWallet($user);

        $this->user_score           = $user->total_score;
        $this->user_remaining_score = $this->user_score;
    }

    private function getPaymentConfig()
    {
        $memberScoreArray = AppConfigLogic::getPaymentConfig();
        if (empty($memberScoreArray)) {
            return false;
        }
        $this->score_price  = 0;
        $this->score_status = $memberScoreArray["score_status"];

        if ($this->score_status == 1) {
            $this->score_price = number_format(1 / $memberScoreArray["score_price"], 4);
        }

        if (!$this->score_price || !is_numeric($this->score_price) || $this->score_price <= 0) {
            return false;
        }
    }

    public function getItemScore()
    {
        return $this->item['score'] = [
            'use'                  => $this->use_score,
            'use_num'              => $this->user_use_score,//intval($this->user_use_score),
            'deduction_price'      => $this->user_use_score,//intval($this->user_use_score),
            'can_use'              => $this->user_use_score > 0 ? true : false,
            'score_price'          => $this->score_price,
            'user_score'           => $this->user_score,
            'user_remaining_score' => $this->user_remaining_score,
        ];
    }
}