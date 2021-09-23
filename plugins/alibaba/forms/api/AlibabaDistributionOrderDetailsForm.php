<?php

namespace app\plugins\alibaba\forms\api;

use app\core\ApiCode;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionOrder;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail;

class AlibabaDistributionOrderDetailsForm extends BaseModel
{
    public $order_id;

    public function rules()
    {
        return [
            [['order_id'], 'required'],
            [['order_id'], 'integer'],
        ];
    }

    /**
     * 获取订单详情
     * @return array
     * @throws \Exception
     */
    public function getOrderDetails()
    {
        try {
            if (!$this->validate()) {
                return $this->returnApiResultData();
            }
            $order = AlibabaDistributionOrder::find()->where(['id' => $this->order_id, 'is_delete' => 0])->asArray()->one();
            if (!$order) throw new \Exception('订单不存在');

            $order['detail'] = AlibabaDistributionOrderDetail::find()->where(['order_id' => $order['id'], 'is_delete' => 0])->asArray()->all();
            if ($order['detail']) {
                foreach ($order['detail'] as &$item) {
                    $goods = AlibabaDistributionGoodsList::findOne(['id' => $item['goods_id']]);
                    $item['name'] = $goods['name'];
                    $item['cover_url'] = $goods['cover_url'];
                    $item['sku_labels'] = json_decode($item['sku_labels'], true);
                }
            }
            $order['created_at'] = date('Y-m-d H:i:s', $order['created_at']);
            $order['pay_at'] = date('Y-m-d H:i:s', $order['pay_at']);
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'', $order);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }
}