<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 删除订单
 * Author: zal
 * Date: 2020-04-17
 * Time: 14:11
 */

namespace app\forms\mall\order;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Order;

class OrderDestroyForm extends BaseModel
{
    public $order_id;
    public $is_recycle;

    public function rules()
    {
        return [
            [['order_id'], 'required'],
            [['order_id', 'is_recycle'], 'integer'],
        ];
    }

    /**
     * 删除订单
     * @return array
     */
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $order = Order::findOne([
                'id' => $this->order_id,
                'is_recycle' => 1,
                'mall_id' => \Yii::$app->mall->id,
            ]);

            if (!$order) {
                throw new \Exception('订单不存在，请刷新后重试');
            }

            if ($order->status == 0) {
                throw new \Exception('订单进行中,不能进行操作');
            }

            $order->is_delete = 1;
            $order->save();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

    /**
     * 移入移出回收站
     * @return array
     */
    public function recycle()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $order = Order::findOne([
            'id' => $this->order_id,
            'mall_id' => \Yii::$app->mall->id,
        ]);

        if (!$order) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '订单不存在，请刷新后重试',
            ];
        }

        $order->is_recycle = $this->is_recycle;
        $order->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '操作成功'
        ];
    }

    /**
     * 清空回收站
     * @return array
     */
    public function destroyAll()
    {
        $where = [
            'is_recycle' => 1,
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
            'is_delete' => 0
        ];
        if ($this->sign) {
            $where['sign'] = $this->sign;
        }
        $count = Order::updateAll(
            ['is_delete' => 1, 'deleted_at' => time()],
            $where
        );
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => "已清空，共删除{$count}个订单"
        ];
    }
}
