<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 卡券发放公共类
 * Author: zal
 * Date: 2020-04-16
 * Time: 14:16
 */

namespace app\forms\common\card;

use app\component\jobs\UserCardCreatedJob;
use app\models\BaseModel;
use app\models\GoodsCards;
use app\models\OrderDetail;
use app\models\UserCard;
use yii\db\Exception;

class SendCommon extends BaseModel
{
    public $mall_id;
    public $user_id;
    public $order_id;

    const hour = 6; //hour

    /**
     * 保存
     * @return array
     * @throws Exception
     */
    public function save()
    {
        $goodsList = OrderDetail::find()->with('card')->where([
            'is_delete' => 0, 'order_id' => $this->order_id
            ])->all();
        if (!$goodsList) {
            throw new Exception('商品不存在，无效的order_id');
        }
        $cardList = [];
        /* @var $goodsList OrderDetail[]*/
        foreach ($goodsList as $item) {
            /* @var GoodsCards[] $goodsCardList*/
            $goodsCardList = $item->goodsCard;
            if (empty($goodsCardList)) {
                continue;
            }
            foreach ($goodsCardList as $card) {
                $count = 0;
                while ($count < bcmul($item->num, $card->num)) {
                    $value = $card->goodsCards;
                    $t = \Yii::$app->db->beginTransaction();
                    try {
                        $value->updateCount('sub', 1);
                        if ($value->expire_type == 1) {
                            $endTime = date('Y-m-d H:i:s', time() + $value->expire_day * 86400);
                        } else {
                            $endTime = $value->end_at;
                        }
                        $userCard = new UserCard();
                        $userCard->mall_id = $this->mall_id;
                        $userCard->user_id = $this->user_id;
                        $userCard->card_id = $value->id;
                        $userCard->name = $value->name;
                        $userCard->pic_url = $value->pic_url;
                        $userCard->content = $value->description;
                        $userCard->created_at = mysql_timestamp();
                        $userCard->is_use = 0;
                        $userCard->clerk_id = 0;
                        $userCard->store_id = 0;
                        $userCard->clerked_at = '0000-00-00 00:00:00';
                        $userCard->order_id = $this->order_id;
                        $userCard->order_detail_id = $item->id;
                        $userCard->data = '';
                        $userCard->start_time = $value->expire_type == 1 ? mysql_timestamp() : $value->begin_time;
                        $userCard->end_at = $endTime;
                        $userCard->save();
                        $cardList[] = $userCard;

                        $interval = self::hour * 3600;
                        $diff = strtotime($endTime) - time();
                        $diff = $diff > $interval ? $diff - $interval : 0;

                        \Yii::$app->queue->delay($diff)->push(new UserCardCreatedJob([
                            'mall' => \Yii::$app->mall,
                            'id' => $userCard->id,
                            'user_id' => $this->user_id,
                        ]));
                        $t->commit();
                    } catch (Exception $exception) {
                        $t->rollBack();
                        \Yii::error('卡券发放失败');
                        \Yii::error($exception);
                    }
                    $count++;
                }
            }
        }
        return $cardList;
    }
}
