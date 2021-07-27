<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 拼团订单form
 * Author: xuyaoxiang
 * Date: 2020/9/12
 * Time: 14:24
 */

namespace app\plugins\group_buy\forms\api;

use app\core\ApiCode;
use app\forms\api\order\OrderForm as ParentOrderForm;
use app\plugins\group_buy\forms\common\OrderListCommon;
use app\logic\CommonLogic;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderDetailExpress;
use app\models\OrderDetailExpressRelation;
use yii\helpers\ArrayHelper;

class OrderForm extends ParentOrderForm
{
    public $active_status;
    public function rules()
    {
        $return = [
            [['active_status'], 'integer']
        ];
        return array_merge($return, parent::rules());
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
        $form             = new OrderListCommon();
        $form->user_id    = \Yii::$app->user->id;
        $form->status     = $this->status;
        if(!empty($this->status)){
            $form->sale_status = 0;
        }
        $form->is_detail  = 1;
        $form->is_goods   = 1;
        $form->is_comment = 1;
        $form->page       = $this->page;
        $form->is_recycle = 0;
       // $form->is_array = 1;
        $form->active_status = $this->active_status;
        $form->relations  = ['detailExpress.expressRelation.orderDetail', 'detail.expressRelation', 'detailExpressRelation.orderExpress'];
        $list             = $form->search();

        $newList = [];
        $order   = new Order();
        /* @var Order[] $list */
        foreach ($list as $item) {
            $newItem                = ArrayHelper::toArray($item);
            $newItem['active_item']    = $item->activeItem ? ArrayHelper::toArray($item->activeItem) : [];
            $newItem['active']    = $item->active ? ArrayHelper::toArray($item->active) : [];
            $newItem['comments']    = $item->comments ? ArrayHelper::toArray($item->comments) : [];
            $newItem['detail']      = $item->detail ? ArrayHelper::toArray($item->detail) : [];
            $newItem['status_text'] = $order->orderStatusText($item);

            if(isset($newItem['active']['status'])){
                if ($newItem['active']['status'] == 1 && $newItem['status'] == 1) {
                    $newItem['status_text'] = "待分享";
                }
            }

            $orderGoodsTotal        = $refundOrderGoodsTotal = 0;
            foreach ($item->detail as $key => $orderDetail) {
                $goodsInfo                             = $this->getGoodsData($orderDetail);
                $newItem['detail'][$key]['goods_info'] = $goodsInfo;
                if ($orderDetail->refund_status == OrderDetail::REFUND_STATUS_SALES || $orderDetail->is_refund == OrderDetail::IS_REFUND_YES) {
                    $refundOrderGoodsTotal++;
                }
                $orderGoodsTotal++;
                CommonLogic::unsetArrayKey($newItem['detail'][$key], ["created_at", "updated_at", "is_delete", "deleted_at"]);
            }
            $newItem["sale_status"] = Order::IS_SALE_NO;
            if ($orderGoodsTotal == $refundOrderGoodsTotal) {
                $newItem["sale_status"] = Order::IS_SALE_YES;
            }

            $newItem["created_at"] = date("Y-m-d H:i:s", $item["created_at"]);
            // 兼容发货方式
            $newItem['is_offline'] = $item->send_type;
            $detailExpressRelation = [];
            foreach ($item->detailExpressRelation as $der) {
                $newDerItem                 = ArrayHelper::toArray($der);
                $newDerItem['orderExpress'] = $der->orderExpress ? ArrayHelper::toArray($der->orderExpress) : [];
                $detailExpressRelation[]    = $newDerItem;
            }

            $newDetailExpress = [];
            /** @var OrderDetailExpress $detailExpress */
            foreach ($item->detailExpress as $detailExpress) {
                $newDeItem          = ArrayHelper::toArray($detailExpress);
                $newExpressRelation = [];
                /** @var OrderDetailExpressRelation $erItem */
                foreach ($detailExpress->expressRelation as $erItem) {
                    $newErItem                              = ArrayHelper::toArray($erItem);
                    $newErItem['orderDetail']               = $erItem->orderDetail ? ArrayHelper::toArray($erItem->orderDetail) : [];
                    $newErItem['orderDetail']['goods_info'] = $erItem->orderDetail ? \Yii::$app->serializer->decode($erItem->orderDetail->goods_info) : [];
                    $newExpressRelation[]                   = $newErItem;
                }
                $newDeItem['expressRelation'] = $newExpressRelation;
                $newDetailExpress[]           = $newDeItem;
            }
            $newItem['detailExpress'] = $newDetailExpress;

            $newItem['der_info']  = $detailExpressRelation;
            $newItem["location"]  = $item["location"] == null ? "" : $item["location"];
            $newItem["city_name"] = $item["location"] == null ? "" : $item["city_name"];
            $newItem["city_info"] = $item["city_info"] == null ? "" : $item["city_info"];

            CommonLogic::unsetArrayKey($newItem, ["updated_at", "is_delete", "deleted_at", "token", "order_form", "words", "seller_remark", "is_pay", "pay_type",
                                                  "is_send", "send_at", "customer_name", "express", "express_no", "is_sale", "is_confirm", "auto_cancel_at",
                                                  "auto_confirm_at", "auto_sales_at", "distance", "city_mobile", "location", "city_name", "city_info", "clerk_id",
                                                  "store_id", "sign", "is_comment", "comment_at", "back_price", "offline_qrcode", "support_pay_types",
                                                  "pay_at", "cancel_status", "score_deduction_price", "send_type", "confirm_at", "name", "mobile", "address",
                                                  "remark", "is_recycle"]);
            $newList[] = $newItem;
        }

        $pageData = $this->getPaginationInfo($form->pagination);
        $data     = ['list' => $newList, 'pagination' => $pageData,];
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '请求成功', $data);
    }
}