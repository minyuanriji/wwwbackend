<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单api-订单查询
 * Author: zal
 * Date: 2020-04-21
 * Time: 14:50
 */

namespace app\forms\api\order;

use app\core\ApiCode;
use app\core\mail\SendMail;
use app\events\OrderEvent;
use app\forms\common\order\OrderCommon;
use app\forms\common\order\OrderListCommon;
use app\forms\common\SmsCommon;
use app\forms\common\mptemplate\MpTplMsgDSend;
use app\forms\common\mptemplate\MpTplMsgSend;
use app\logic\AppConfigLogic;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderDetailExpress;
use app\models\OrderDetailExpressRelation;
use app\models\OrderRefund;
use app\models\Store;
use Overtrue\EasySms\Message;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class OrderForm extends BaseModel
{
    public $page;
    public $limit;
    public $status;
    public $id;// 订单ID
    public $offline;
    public $offline_used;
    public $order_refund_id;
    public $keywords;
    public $mall_id;

    public function rules()
    {
        return [
            [['page', 'limit', 'status', 'id', 'offline', 'offline_used', 'mall_id'], 'integer'],
            ['page', 'default', 'value' => 1],
            ['limit', 'default', 'value' => 20],
            ['order_refund_id', 'safe'],
            ['keywords', 'string'],
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
        $form = new OrderListCommon();
        $form->user_id = \Yii::$app->user->id;
        $form->status = $this->status;
        if(!empty($this->status)){
            $form->sale_status = 0;
        }
        $form->is_detail = 1;
        $form->mall_id = $this->mall_id ?: \Yii::$app->mall->id;
        $form->is_goods = 1;
        $form->is_comment = 1;
        $form->page = $this->page;
        $form->is_recycle = 0;
        $form->relations = ['mch.store', 'detailExpress.expressRelation.orderDetail','detail.expressRelation','detail.refund', 'detailExpressRelation.orderExpress', 'mall'];

        if($this->offline){ //核销
            $form->status = null;
            $form->sale_status = null;
            $form->only_offline_order = 1;
            $form->only_offline_used = $this->offline_used ? 1 : 0;
        }else{ //寄送
            $form->only_express_order = 1;
        }

        if ($this->keywords) {
            $form->keywords = $this->keywords;
        }

        $list = $form->search();
        $newList = [];
        $order = new Order();
        /* @var Order[] $list */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $mall = $item->mall ? ArrayHelper::toArray($item->mall) : [];
            $newItem['mall_name'] = $mall['name'] ?: "补商汇官方商城";
            $newItem['mall_logo'] = $mall['logo'] ?: "https://www.mingyuanriji.cn/web/static/app/icon.png";
            $newItem['comments'] = $item->comments ? ArrayHelper::toArray($item->comments) : [];
            $newItem['detail'] = $item->detail ? ArrayHelper::toArray($item->detail) : [];
            $newItem['status_text'] = $order->orderStatusText($item);
            $orderGoodsTotal = $refundOrderGoodsTotal = 0;
            foreach ($item->detail as $key => $orderDetail) {
                $goodsInfo = $this->getGoodsData($orderDetail);
                $newItem['detail'][$key]['goods_info'] = $goodsInfo;
                if($orderDetail->refund_status == OrderDetail::REFUND_STATUS_SALES || $orderDetail->is_refund == OrderDetail::IS_REFUND_YES){
                    $refundOrderGoodsTotal++;
                }
                $orderGoodsTotal++;

                //自定义订单退款状态1退款中2已退款3退款退货中4已退款退货5换货中6换货完成
                $orderRefundStatus = OrderCommon::getDiyOrderRefundStatus($orderDetail);
                $newItem['detail'][$key]['diy_refund_status'] = $orderRefundStatus;
                CommonLogic::unsetArrayKey($newItem['detail'][$key],["created_at","updated_at","is_delete","deleted_at"]);
            }
            $newItem["sale_status"] = Order::IS_SALE_NO;
            if($orderGoodsTotal == $refundOrderGoodsTotal){
                $newItem["sale_status"] = Order::IS_SALE_YES;
            }

            $newItem["created_at"] = date("Y-m-d H:i:s",$item["created_at"]);
            // 兼容发货方式
            $newItem['is_offline'] = $item->send_type;
            $detailExpressRelation = [];
            foreach ($item->detailExpressRelation as $der) {
                $newDerItem = ArrayHelper::toArray($der);
                $newDerItem['orderExpress'] = $der->orderExpress ? ArrayHelper::toArray($der->orderExpress) : [];
                $detailExpressRelation[] = $newDerItem;
            }

            $newDetailExpress = [];
            /** @var OrderDetailExpress $detailExpress */
            foreach ($item->detailExpress as $detailExpress) {
                $newDeItem = ArrayHelper::toArray($detailExpress);
                $newExpressRelation = [];
                /** @var OrderDetailExpressRelation $erItem */
                foreach ($detailExpress->expressRelation as $erItem) {
                    $newErItem = ArrayHelper::toArray($erItem);
                    $newErItem['orderDetail'] = $erItem->orderDetail ? ArrayHelper::toArray($erItem->orderDetail) : [];
                    $newErItem['orderDetail']['goods_info'] = $erItem->orderDetail ? \Yii::$app->serializer->decode($erItem->orderDetail->goods_info) : [];
                    $newExpressRelation[] = $newErItem;
                }
                $newDeItem['expressRelation'] = $newExpressRelation;
                $newDetailExpress[] = $newDeItem;
            }
            $newItem['detailExpress'] = $newDetailExpress;
            $newItem['der_info'] = $detailExpressRelation;
            $newItem["location"] = $item["location"] == null ? "" : $item["location"];
            $newItem["city_name"] = $item["location"] == null ? "" : $item["city_name"];
            $newItem["city_info"] = $item["city_info"] == null ? "" : $item["city_info"];

             CommonLogic::unsetArrayKey($newItem,["updated_at","is_delete","deleted_at","token","order_form","words","seller_remark","is_pay","pay_type",
                                                        "is_send","send_at","customer_name","express","express_no","is_sale","is_confirm","auto_cancel_at",
                                                        "auto_confirm_at","auto_sales_at","distance","city_mobile","location","city_name","city_info","clerk_id",
                                                        "store_id","sign","is_comment","comment_at","back_price","offline_qrcode","support_pay_types",
                                                        "pay_at","score_deduction_price","send_type","confirm_at","name","mobile","address",
                                                        "remark","is_recycle"]);
             //商家信息
            $newItem['is_mch']   = 0;
            $newItem['mch_info'] = [];
            if($newItem['mch_id']){
                $mchStore = Store::findOne(['mch_id' => (int)$newItem['mch_id']]);
                if($mchStore){
                    $newItem['is_mch']   = 1;
                    $newItem['mch_info'] = $mchStore->getAttributes();
                }
            }

             $newList[] = $newItem;
        }
        //$tpl = ['order_pay_tpl', 'order_cancel_tpl', 'order_send_tpl'];
        $pageData = $this->getPaginationInfo($form->pagination);
        $data = ['list' => $newList, 'pagination' => $pageData,];//'template_message' => TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, $tpl)
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'请求成功',$data);
    }

    /**
     * 售后订单列表
     * @return array
     */
    public function getRefundOrderList()
    {
        try {
            $list = OrderRefund::find()->where([
//                'mall_id' => $this->mall_id ?: \Yii::$app->mall->id,
                'user_id' => \Yii::$app->user->id,
                'is_delete' => 0,
            ])->with(['detail.goods.goodsWarehouse'])
              ->page($pagination)
              ->orderBy('id DESC')
              ->all();
            $orderRefund = new OrderRefund();
            $newList = [];
            /** @var OrderRefund $item */
            foreach ($list as $item) {
                $newItem = ArrayHelper::toArray($item);
                $newItem['status_text'] = $orderRefund->statusText($item);
                $goodsInfo = $this->getGoodsData($item->detail);
                $newItem['detail'][] = ['goods_info' => $goodsInfo];
                $newItem = array_merge($newItem, $item->checkAfterRefund($item));
                $newItem["created_at"] = !empty($order["created_at"]) ? date("Y-m-d H:i:s",$order["created_at"]):"";
                $newItem["send_at"] = !empty($order["send_at"]) ? date("Y-m-d H:i:s",$order["send_at"]):"";
                $newItem["status_at"] = !empty($order["status_at"]) ? date("Y-m-d H:i:s",$order["status_at"]):"";
                $newItem["confirm_at"] = !empty($order["confirm_at"]) ? date("Y-m-d H:i:s",$order["confirm_at"]):"";
                $newItem["refund_at"] = !empty($order["confirm_at"]) ? date("Y-m-d H:i:s",$order["refund_at"]):"";
                CommonLogic::unsetArrayKey($newItem,["updated_at","is_delete","deleted_at"]);
                $newList[] = $newItem;
            }
            $data = [];
            $data["list"] = $newList;
            $data["pagination"] = $this->getPaginationInfo($pagination);
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'请求成功',$data);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }

    /**
     * 处理订单列表展示的商品数据
     * @param OrderDetail $orderDetail
     * @return array
     */
    protected function getGoodsData($orderDetail)
    {
        $goodsInfo = [];
        try {
            $goodsAttrInfo = \Yii::$app->serializer->decode($orderDetail->goods_info);
            $goodsInfo['name'] = isset($goodsAttrInfo['goods_attr']['name']) ? $goodsAttrInfo['goods_attr']['name'] : '';
            $goodsInfo['attr_list'] = isset($goodsAttrInfo['attr_list']) ? $goodsAttrInfo['attr_list'] : [];
            $goodsInfo['pic_url'] = isset($goodsAttrInfo['goods_attr']['pic_url']) && $goodsAttrInfo['goods_attr']['pic_url'] ? $goodsAttrInfo['goods_attr']['pic_url'] : $goodsAttrInfo['goods_attr']['cover_pic'];
            $goodsInfo["unit_price"] = $orderDetail->unit_price;
            $goodsInfo['num'] = isset($orderDetail->num) ? $orderDetail->num : 0;
            $goodsInfo['total_original_price'] = isset($orderDetail->total_original_price) ? $orderDetail->total_original_price : 0;
            $goodsInfo['member_discount_price'] = isset($orderDetail->member_discount_price) ? $orderDetail->member_discount_price : 0;

        } catch (\Exception $exception) {
            // dd($exception);
        }
        return $goodsInfo;
    }

    /**
     * 售后订单取消
     * @return array
     */
    public function DelRefundOrder()
    {
        try {
            /* @var Order $order */
            $order = OrderRefund::find()->where([
//                'mall_id' => \Yii::$app->mall->id,
                'user_id' => \Yii::$app->user->id,
                'is_delete' => 0,
                'id' => $this->order_refund_id,
            ])->one();

            if (!$order) {
                throw new \Exception('售后订单数据异常');
            }
            $order->is_delete = 1;
            $res = $order->save();
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($order));
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'删除成功');
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }

}
