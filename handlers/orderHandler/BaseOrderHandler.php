<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单处理基础类
 * Author: zal
 * Date: 2020-04-21
 * Time: 16:10
 */

namespace app\handlers\orderHandler;

use app\events\OrderEvent;
use app\forms\common\prints\Exceptions\PrintException;
use app\forms\common\prints\PrintOrder;
use app\models\BaseModel;
use app\models\Mall;
use app\forms\OrderConfig;
use yii\db\Exception;

/**
 * @property OrderEvent $event
 * @property Mall $mall
 * @property OrderConfig $orderConfig
 */
abstract class BaseOrderHandler extends BaseModel
{
    public $event;
    public $mall;
    public $orderConfig;

    /**
     * @return mixed
     * 事件处理
     */
    abstract public function handle();

    /**
     * @return $this
     */
    public function setMall()
    {
        try {
            $this->mall = \Yii::$app->mall;
        } catch (\Exception $exception) {
            $mall = Mall::findOne(['id' => $this->event->order->mall_id]);
            \Yii::$app->setMall($mall);
            $this->mall = \Yii::$app->mall;
        }
        return $this;
    }

    /**
     * 小票打印
     * @param string $orderType submit|pay|confirm 打印方式
     * @return $this
     *
     */
    protected function receiptPrint($orderType)
    {
        try {
            if ($this->orderConfig->is_print != 1) {
                throw new \Exception($this->event->order->sign . '未开启小票打印');
            }
            $printer = new PrintOrder();
            $printer->print($this->event->order, $this->event->order->id, $orderType);
        } catch (PrintException $exception) {
            \Yii::error('小票打印机打印:' . $exception->getMessage());
        } catch (\Exception $exception) {
            \Yii::error('小票打印机打印:' . $exception->getMessage());
        }
        return $this;
    }

    public function setMchId()
    {
        \Yii::$app->setMchId($this->event->order->mch_id);
        return $this;
    }
}
