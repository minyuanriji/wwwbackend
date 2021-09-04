<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单api-订单详情
 * Author: zal
 * Date: 2020-05-11
 * Time: 10:50
 */

namespace app\forms\api\order;

use app\core\ApiCode;
use app\forms\api\goods\MallGoods;
use app\forms\common\DeliveryCommon;
use app\forms\common\order\OrderCommon;
use app\forms\common\order\OrderDetailCommon;
use app\forms\common\template\TemplateList;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\plugins\Plugin;

class OrderDetailForm extends BaseModel
{
    public $id;// 订单ID
    public $action_type;//操作订单的类型,1 订单核销详情|

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer'],
            [['action_type'], 'string'],
        ];
    }

    /**
     * 订单详情数据
     * @return array
     */
    public function getDetail()
    {
        try {
            if (!$this->validate()) {
                return $this->responseErrorInfo();
            }

            $form = new OrderDetailCommon();
            $form->id = $this->id;
            $form->is_goods = 1;
            $form->is_refund = 1;
            $form->is_array = 1;
            $order = $form->search();
            if (!$order) {
                throw new \Exception('订单不存在');
            }

            $goodsNum = 0;
            $memberDeductionPriceCount = 0;
            // 统一商品信息，用于前端展示
            $orderRefund = new OrderRefund();
            //订单详情数据
            $detail = $orderDetailData = [];
            $detail["id"]                            = intval($order["id"]);
            $detail["status"]                        = intval($order["status"]);
            $detail["order_no"]                      = $order["order_no"];
            $detail["express_no"]                    = $order["express_no"];
            $detail["total_pay_price"]               = $order["total_pay_price"];
            $detail["total_goods_original_price"]    = $order["total_goods_original_price"];
            $detail["express_price"]                 = $order["express_price"];
            $detail["coupon_discount_price"]         = $order["coupon_discount_price"];
            $detail["score_deduction_price"]         = $order["integral_deduction_price"];
            $detail["shopping_voucher_decode_price"] = $order["shopping_voucher_decode_price"];
            $detail["name"]                          = $order["name"];
            $detail["mobile"]                        = $order["mobile"];
            $detail["address"]                       = $order["address"];
            $detail["remark"]                        = $order["remark"];
            $detail["is_pay"]                        = intval($order["is_pay"]);
            $detail['order_type']                    = $order['order_type'];

            $orderModel = new Order();
            // 订单状态
            $detail['status_text'] = $orderModel->orderStatusText($order);
            $detail['pay_type_text'] = $orderModel->getPayTypeText($order['pay_type']);
            $detail['send_type_text'] = $orderModel->getSendTypeText($order['send_type']);
            $detail["created_at"] = date("Y-m-d H:i:s",$order["created_at"]);
            $detail["pay_at"] = !empty($order["pay_at"]) ? date("Y-m-d H:i:s",$order["pay_at"]):"";
            $detail["send_at"] = !empty($order["send_at"]) ? date("Y-m-d H:i:s",$order["send_at"]):"";
            //确认收货时间
            $confirm_at = !empty($order["confirm_at"]) ? date("Y-m-d H:i:s",$order["confirm_at"]):"";
            $confirm_at = empty($confirm_at) ? (!empty($order["auto_confirm_at"]) ? date("Y-m-d H:i:s",$order["auto_confirm_at"]):"") : $confirm_at;
            $detail["confirm_at"] = $confirm_at;
            $detail["comment_at"] = !empty($order["comment_at"]) ? date("Y-m-d H:i:s",$order["comment_at"]):"";
            $detail["is_comment"] = intval($order["is_comment"]);
            //取消时间
            $cancel_at = !empty($order["cancel_at"]) ? date("Y-m-d H:i:s",$order["cancel_at"]):"";
            $cancel_at = empty($cancel_at) ? (!empty($order["auto_cancel_at"]) ? date("Y-m-d H:i:s",$order["auto_cancel_at"]):"") : $cancel_at;
            $detail["cancel_at"] = $cancel_at;
            // 订单商品总数
            $detail['goods_num'] = 0;
            $detail['member_deduction_price_count'] = 0;
            //$detail['city'] = json_decode($order['city_info'], true);
            if ($order['send_type'] == 2) {
                $detail['delivery_config'] = DeliveryCommon::getInstance()->getConfig();
            }
            //插件数据
            $detail["plugin_data"] = $this->getPluginData($order["id"]);
            //订单商品数据
            $orderGoodsTotal = $refundOrderGoodsTotal = 0;
            foreach ($order['detail'] as $key => $item) {
                $goodsNum += $item['num'];
                $memberDeductionPriceCount += $item['member_discount_price'];
                $goodsInfo = MallGoods::getGoodsData($item);
                // 售后订单 状态
                if (isset($item['refund'])) {
                    $item['refund']['status_text'] = $orderRefund->statusText($item['refund']);
                }
                $item["diy_refund_status"] = OrderCommon::getDiyOrderRefundStatus($item);
                $item['goods_info'] = $goodsInfo;
                CommonLogic::unsetArrayKey($item,["created_at","updated_at","is_delete","deleted_at","token","order_form","words","seller_remark","form_data",
                                                    "form_id","goods_no"]);
                $item["refund"] = $item["refund"] == null ? [] : $item["refund"];

                if($item["refund_status"] == OrderDetail::REFUND_STATUS_SALES || $item["is_refund"] == OrderDetail::IS_REFUND_YES){
                    $refundOrderGoodsTotal++;
                }
                $orderGoodsTotal++;
                $item['is_on_site_consumption'] = 0;
                if(!empty($item['orderGoodsConsumeVerification'])){
                    $item['is_on_site_consumption'] = 1;
                }
                $detail["order_goods_list"][] = $item;
            }
            $express_no	= $express_code = $express = $mobile = "";
            if(isset($order["detailExpress"][0]) && !empty($order["detailExpress"][0])){
                $express_no = $order["detailExpress"][0]["express_no"];
                $express_code = $order["detailExpress"][0]["express_code"];
                $express = $order["detailExpress"][0]["express"];
                unset($order["detailExpress"]);
            }
            $detail["sale_status"] = Order::IS_SALE_NO;
            if($orderGoodsTotal == $refundOrderGoodsTotal){
                $detail["sale_status"] = Order::IS_SALE_YES;
            }
            $detail["express_no"] = $express_no;
            $detail["express_code"] = $express_code;
            $detail["express"] = $express;
            $detail['goods_num'] = $goodsNum;
            $detail['member_deduction_price_total'] = price_format($memberDeductionPriceCount);

            $orderDetailData["detail"] = $detail;

            //商家数据
            $orderDetailData['is_mch'] = 0;
            $orderDetailData['mch'] = [];
            if(!empty($order['mch']['store'])){
                $orderDetailData['is_mch'] = 1;
                $orderDetailData['mch']['mch_id']           = $order['mch']['store']['mch_id'];
                $orderDetailData['mch']['name']             = $order['mch']['store']['name'];
                $orderDetailData['mch']['mobile']           = $order['mch']['store']['mobile'];
                $orderDetailData['mch']['address']          = $order['mch']['store']['address'];
                $orderDetailData['mch']['province_id']      = $order['mch']['store']['province_id'];
                $orderDetailData['mch']['city_id']          = $order['mch']['store']['city_id'];
                $orderDetailData['mch']['district_id']      = $order['mch']['store']['district_id'];
                $orderDetailData['mch']['longitude']        = $order['mch']['store']['longitude'];
                $orderDetailData['mch']['latitude']         = $order['mch']['store']['latitude'];
                $orderDetailData['mch']['score']            = $order['mch']['store']['score'];
                $orderDetailData['mch']['cover_url']        = $order['mch']['store']['cover_url'];
                $orderDetailData['mch']['pic_url']          = $order['mch']['store']['pic_url'];
                $orderDetailData['mch']['business_hours']   = $order['mch']['store']['business_hours'];
                $orderDetailData['mch']['description']      = $order['mch']['store']['description'];
                $orderDetailData['mch']['scope']            = $order['mch']['store']['scope'];
            }



            //兼容旧版本
            $orderDetailData['is_need_address'] = 1;
            if(in_array($detail['order_type'], ["offline_normal", "offline_baopin"])){
                $orderDetailData['is_need_address'] = 0;
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'请求成功',$orderDetailData);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }

    private function getTemplateMessage()
    {
        $arr = ['order_cancel_tpl'];
        $list = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, $arr);
        return $list;
    }

    /**
     * 获取插件相关订单数据
     * @param $order_id
     * @return array
     */
    private function getPluginData($order_id){
        $plugins = \Yii::$app->plugin->list;
        $plugin_data = [];
        $newData = [];

        foreach ($plugins as $plugin) {
            $PluginClass = 'app\\plugins\\' . $plugin->name . '\\Plugin';
            /** @var Plugin $pluginObject */
            if (!class_exists($PluginClass)) {
                continue;
            }
            $object = new $PluginClass();
            if (method_exists($object, 'getOrderInfo')) {
                $data = $object->getOrderInfo($order_id);
                if ($data && is_array($data)) {
                    foreach ($data as $datum) {
                        $newData[] = $datum;
                    }
                }
                $plugin_data = $newData;
            }
        }
        return $plugin_data;
    }
}
