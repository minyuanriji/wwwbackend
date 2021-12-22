<?php

namespace app\forms\mall\finance;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Order;
use app\models\OrderDetail;
use app\models\Store;
use app\models\User;
use app\plugins\giftpacks\models\GiftpacksItem;
use app\plugins\giftpacks\models\GiftpacksOrder;
use app\plugins\giftpacks\models\GiftpacksOrderItem;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchPriceLog;

class MchPriceLogListForm extends BaseModel{

    public $page;
    public $limit;
    public $start_date;
    public $end_date;
    public $keyword;
    public $kw_type;
    public $status;

    public function rules(){
        return [
            [['page', 'limit'], 'integer'],
            [['keyword', 'status', 'start_date', 'end_date', 'kw_type'], 'trim']
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $startTime = strtotime($this->start_date);
            $endTime = strtotime($this->end_date);

            $query = MchPriceLog::find()->alias("mpl")
                ->leftJoin(["m" => Mch::tableName()], "m.id=mpl.mch_id")
                ->leftJoin(["s" => Store::tableName()], "s.id=mpl.store_id");

            $query->where(["mpl.status" => $this->status]);

            //日期搜索
            if($startTime > 0){
                $query->andWhere([">", "mpl.created_at", $startTime]);
            }

            if($endTime > 0){
                $query->andWhere(["<", "mpl.created_at", $endTime]);
            }

            //关键词搜索
            if($this->keyword){
                switch ($this->kw_type){
                case "store_name": //商家昵称
                    $query->andWhere(["like", "s.name", $this->keyword]);
                    break;
                case "mch_id": //商家ID
                    $query->andWhere(["m.id" => $this->keyword]);
                    break;
                case "mch_mobile": //商家手机号
                    $query->andWhere(["m.mobile" => $this->keyword]);
                    break;
                case "order_no": //订单编号
                    $giftOrder = GiftpacksOrder::find()->where(['like', 'order_sn', $this->keyword])->select('id');
                    $giftOrderItemIds = GiftpacksOrderItem::find()->where(['order_id' => $giftOrder])->select('id');

                    $orderIds = Order::find()->where(['like', 'order_no', $this->keyword])->select('id');
                    $detailIds = OrderDetail::find()->where(['order_id' => $orderIds])->select('id');
                    $query->andWhere([
                        'or',
                        ['source_id' => $giftOrderItemIds],
                        ['source_id' => $detailIds]
                    ]);
                    break;
                case 'pay_user_id':
                    $giftOrder = GiftpacksOrder::find()->where(['user_id' => $this->keyword])->select('id');
                    $giftOrderItemIds = GiftpacksOrderItem::find()->where(['order_id' => $giftOrder])->select('id');

                    $orderIds = Order::find()->where(['user_id' => $this->keyword])->select('id');
                    $detailIds = OrderDetail::find()->where(['order_id' => $orderIds])->select('id');
                    $query->andWhere([
                        'or',
                        ['source_id' => $giftOrderItemIds],
                        ['source_id' => $detailIds]
                    ]);
                    break;
                default;
                }
            }

            $query->select(["mpl.*", "m.transfer_rate", "m.mobile", "s.name as store_name", "s.cover_url"]);

            $list = $query->orderBy("mpl.id desc")->page($pagination, 10, $this->page)->asArray()->all();
            if($list){
                foreach($list as &$item){
                    $otherData = @json_decode($item['other_json_data'], true);
                    $item['transfer_rate'] = isset($otherData['transfer_rate']) ? $otherData['transfer_rate'] : $item['transfer_rate'];
                    $item['user'] = [];
                    $item['summary_price_calculation'] = 0;
                    $item['goods_count'] = 0;
                    $item['order_item_info'] = [];
                    $item['goods_info'] = [];
                    //大礼包
                    if($item['source_type'] == "giftpacks_order_item"){
                        $orderItemInfo = GiftpacksOrderItem::find()->with([
                            "giftpacksItem", "giftpackOrder", "giftpackOrder.giftpacks", "giftpackOrder.user"
                        ])->where(["id" => $item['source_id']])->asArray()->one();
                        $item['order_item_info'] = $orderItemInfo ?: [];
                        if(isset($item['order_item_info']['giftpackOrder'])){
                            $item['order_item_info']['giftpackOrder']['integral_deduction_price'] = (float)$item['order_item_info']['giftpackOrder']['integral_deduction_price'];
                            $item['user'] = isset($item['order_item_info']['giftpackOrder']['user']) ? $item['order_item_info']['giftpackOrder']['user'] : [];
                        }
                        if(isset($item['order_item_info']['giftpackOrder']['giftpacks']['score_give_settings'])){
                            $item['order_item_info']['giftpackOrder']['giftpacks']['score_give_settings'] = @json_decode($item['order_item_info']['giftpackOrder']['giftpacks']['score_give_settings'], true);
                        }
                        $giftpacksItem = GiftpacksItem::findOne($item['order_item_info']['giftpacksItem']['id']);
                        if ($giftpacksItem) {
                            $query = GiftpacksItem::find()->where([
                                'pack_id' => $giftpacksItem->pack_id
                            ]);
                            $item['summary_price_calculation'] = $query->sum('item_price');
                            $count_query = clone $query;
                            $item['goods_count'] = $count_query->count();
                        }
                    }
                    //商品订单
                    if($item['source_type'] == "order_detail"){
                        $orderDetail = OrderDetail::findOne($item['source_id']);
                        $user = $order = null;
                        if($orderDetail){
                            $order = Order::findOne($orderDetail->order_id);
                        }
                        if($order){
                            $user = User::findOne($order->user_id);
                        }
                        $item['order_detail'] = $orderDetail ? $orderDetail->getAttributes() : [];
                        $item['goods_info'] = $orderDetail ? $orderDetail->decodeGoodsInfo() : [];
                        $item['goods_info']['goods_stock'] = $orderDetail->goods->goods_stock;
                        $item['goods_info']['product'] = $orderDetail->goodsWarehouse->product;
                        $item['order'] = $order ? $order->getAttributes() : [];
                        $item['user'] = $user ? $user->getAttributes() : [];
                        $item['summary_price_calculation'] = $item['price'];
                        $item['goods_count'] = 1;
                    }

                    if (empty($item['cover_url'])) {
                        $item['cover_url'] = 'https://dev.mingyuanriji.cn/web/static/header-logo.png';
                    }
                }
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                'list'       => $list ? $list : [],
                'pagination' => $pagination
            ]);
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}