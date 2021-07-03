<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单api-订单查询
 * Author: zal
 * Date: 2020-05-11
 * Time: 19:55
 */

namespace app\forms\api\order;

use app\core\ApiCode;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\Order;
use app\models\OrderComments;
use app\models\OrderDetail;

class OrderCommentForm extends BaseModel
{
    public $commentData;
    public $order_id;

    public function rules()
    {
        return [
            [['order_id', 'commentData'], 'required'],
            [['order_id'], 'integer'],
        ];
    }

    /**
     * 订单评价
     * @return array
     */
    public function comment()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }

        //$commentItem = $this->commentData;
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (empty($this->commentData) || !is_array($this->commentData)) {
                throw new \Exception('数据异常');
            }

            $order = Order::findOne($this->order_id);
            if (!$order) {
                throw new \Exception('订单不存在');
            }
            if ($order->status != Order::STATUS_WAIT_COMMENT) {
                throw new \Exception('该订单状态不能进行评价...');
            }
            foreach ($this->commentData as $commentItem) {
                /** @var OrderDetail $orderDetail */
                $orderDetail = OrderDetail::getOneData(['id' => $commentItem['order_detail_id']]);

                if (!$orderDetail) {
                    throw new \Exception('订单不存在');
                }

                $orderComments = OrderComments::find()->where(['order_detail_id' => $orderDetail->id])->one();
                if ($orderComments) {
                    throw new \Exception('订单已评价,无需再次评价');
                } else {
                    $orderComments = new OrderComments();
                }
                $orderComments->mall_id = \Yii::$app->mall->id;
                $orderComments->mch_id = $order->mch_id;
                $orderComments->order_detail_id = $orderDetail->id;
                $orderComments->order_id = $orderDetail->order_id;
                $orderComments->sign = $orderDetail->order->sign;
                $orderComments->user_id = \Yii::$app->user->id;
                $orderComments->score = $commentItem['grade_level'];
                $orderComments->content = $commentItem['content'] ? $commentItem['content'] : '此用户没有填写评价';
                $orderComments->pic_url = \Yii::$app->serializer->encode($commentItem['pic_list']);
                $orderComments->goods_id = $orderDetail->goods_id;
                $orderComments->goods_warehouse_id = $orderDetail->goods->goods_warehouse_id;
                $orderComments->reply_content = '';
                $orderComments->is_anonymous = isset($commentItem['is_anonymous']) ? $commentItem['is_anonymous'] : OrderComments::IS_ANONYMOUS_NO;
                $res = $orderComments->save();
                if (!$res) {
                    throw new \Exception($this->responseErrorMsg($orderComments));
                }
            }
            //订单状态修改
            $order->status = Order::STATUS_COMPLETE;
            $order->is_comment = 1;
            $order->comment_at = time();
            $res = $order->save();
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($order));
            }
            $transaction->commit();
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'评价成功');
        } catch (\Exception $e) {
            $transaction->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }
}
