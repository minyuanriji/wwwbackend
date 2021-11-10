<?php

namespace app\plugins\alibaba\forms\api;

use app\core\ApiCode;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionOrder;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail1688;
use function Webmozart\Assert\Tests\StaticAnalysis\float;

class AlibabaDistributionOrderListForm extends BaseModel{

    public $page;
    public $limit;
    public $status;
    public $keywords;

    public function rules()
    {
        return [
            [['page', 'limit'], 'integer'],
            [['status'], 'safe'],
            [['keywords'], 'string'],
        ];
    }

    /**
     * 获取订单列表
     * @return array
     * @throws \Exception
     */
    public function getOrderList(){
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }

        try {

            $query = AlibabaDistributionOrder::find();
            $query->where([
                "mall_id"   => \Yii::$app->mall->id,
                "user_id"   => \Yii::$app->user->id,
                "is_delete" => 0
            ]);
            $selects = ["id", "order_no", "total_price", "total_pay_price", "express_original_price", "express_price", "total_goods_price",
                "total_goods_original_price", "is_pay", "shopping_voucher_use_num", "shopping_voucher_decode_price",
                "shopping_voucher_express_use_num", "shopping_voucher_express_decode_price"
            ];

            if ($this->keywords) {
                $goodsIds = AlibabaDistributionGoodsList::find()->andWhere(['like', 'name', $this->keywords])->select('id');
                $orderIds = AlibabaDistributionOrderDetail::find()->andWhere(['goods_id' => $goodsIds])->select('order_id');
                $query->andWhere(['id' => $orderIds]);
            }

            $orderDatas = $query->select($selects)->asArray()->orderBy("id DESC")->page($pagination, 20, $this->page)->all();
            if($orderDatas){
                $orderIds = $tmpOrderDatas = [];
                foreach($orderDatas as &$orderData){
                    $orderIds[] = $orderData['id'];
                    $tmpOrderDatas[$orderData['id']] = $orderData;
                }

                $query = AlibabaDistributionOrderDetail::find()->alias("od");
                $query->innerJoin(["od1688" => AlibabaDistributionOrderDetail1688::tableName()], "od1688.order_detail_id=od.id");
                $query->innerJoin(["g" => AlibabaDistributionGoodsList::tableName()], "g.id=od.goods_id");
                $query->andWhere(["IN", "od.order_id", $orderIds]);
                $selects = ["od.id", "od.order_id", "od.num", "od.unit_price", "od.total_original_price", "od.total_price", "od.is_refund", "od.refund_status", "od.shopping_voucher_decode_price",
                    "od.shopping_voucher_num", "od.sku_labels",  "g.cover_url", "g.name"
                ];
                $orderDetails = $query->select($selects)->asArray()->all();

                if($orderDetails){
                    foreach($orderDetails as $orderDetail){
                        $orderDetail['sku_labels'] = $orderDetail['sku_labels'] ? @json_decode($orderDetail['sku_labels'], true) : [];
                        $orderDetail['sku_labels'] = $orderDetail['sku_labels'] ? implode(",", $orderDetail['sku_labels']) : "";
                        if(!isset($tmpOrderDatas[$orderDetail['order_id']]['details'])){
                            $tmpOrderDatas[$orderDetail['order_id']]['details'] = [];
                        }

                        $tmpOrderDatas[$orderDetail['order_id']]['details'][] = $orderDetail;
                    }
                    $orderDatas = [];
                    foreach($tmpOrderDatas as $orderData){
                        if(empty($orderData['details'])) continue;
                        $orderData['shopping_voucher_total_use_num'] = floatval($orderData['shopping_voucher_express_use_num']) + floatval($orderData['shopping_voucher_use_num']);
                        $orderDatas[] = $orderData;
                    }
                }
                unset($tmpOrderDatas);
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'', [
                'list'       => $orderDatas ? $orderDatas : [],
                'pagination' => $pagination,
            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, CommonLogic::getExceptionMessage($e));
        }
    }
}