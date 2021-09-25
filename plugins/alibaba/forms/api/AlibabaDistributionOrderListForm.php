<?php

namespace app\plugins\alibaba\forms\api;

use app\core\ApiCode;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionOrder;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail1688;

class AlibabaDistributionOrderListForm extends BaseModel{

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
    public function getOrderList(){
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }


        try {
            $query = AlibabaDistributionOrderDetail1688::find()->alias("od1688");
            $query->innerJoin(["od" => AlibabaDistributionOrderDetail::tableName()], "od.id=od1688.order_detail_id");
            $query->innerJoin(["g" => AlibabaDistributionGoodsList::tableName()], "g.id=od1688.goods_id");
            $query->innerJoin(["o" => AlibabaDistributionOrder::tableName()], "o.id=od1688.order_id");
            $query->where([
                "od.mall_id"     => \Yii::$app->mall->id,
                "od1688.user_id" => \Yii::$app->user->id,
                "o.is_delete"    => 0
            ]);
            $query->select(["od1688.id", "g.cover_url", "g.name", "o.shopping_voucher_express_use_num", "od.num", "od.shopping_voucher_num", "od.sku_labels"]);

            $list = $query->asArray()->orderBy("o.id DESC")->page($pagination, $this->limit, $this->page)->all();

            if ($list) {
                foreach ($list as &$item) {
                    $item['sku_labels'] = $item['sku_labels'] ? @json_decode($item['sku_labels'], true) : [];
                }

            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'', [
                'list' => $list,
                'pagination' => $pagination,
            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, CommonLogic::getExceptionMessage($e));
        }
    }
}