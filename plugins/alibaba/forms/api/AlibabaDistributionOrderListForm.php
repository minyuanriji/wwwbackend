<?php

namespace app\plugins\alibaba\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionOrder;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail;

class AlibabaDistributionOrderListForm extends BaseModel
{
    public $page;
    public $limit;
    public $status;

    public function rules()
    {
        return [
            [['page', 'limit'], 'integer'],
            [['status'], 'safe']
        ];
    }

    /**
     * 获取订单列表
     * @return array
     * @throws \Exception
     */
    public function getOrderList()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        $order = AlibabaDistributionOrder::find()->alias('ao')
                ->leftJoin(['aod' => AlibabaDistributionOrderDetail::tableName()], 'ao.id=aod.order_id')
                ->leftJoin(['aog' => AlibabaDistributionGoodsList::tableName()], 'aog.id=aod.goods_id')
                ->where(['ao.mall_id' => \Yii::$app->mall->id, 'ao.user_id' => \Yii::$app->user->id, 'ao.is_delete' => 0])
                ->select(['ao.*','aod.*', 'aog.name', 'aog.cover_url'])
                ->page($pagination)
                ->orderBy('ao.id DESC')
                ->asArray()
                ->all();
        if ($order) {
            foreach ($order as &$item) {
                $item['sku_labels'] = json_decode($item['sku_labels'], true);
            }
        }
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'', [
            'list' => $order,
            'pagination' => $pagination,
        ]);
    }
}