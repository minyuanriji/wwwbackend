<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单api-订单查询
 * Author: zal
 * Date: 2020-05-13
 * Time: 14:55
 */

namespace app\forms\api\order;

use app\core\ApiCode;
use app\forms\api\goods\MallGoods;
use app\forms\common\template\TemplateList;
use app\logic\AppConfigLogic;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\Express;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use yii\helpers\ArrayHelper;
use app\models\mysql\Order as OrderModel;

class OrderRefundForm extends BaseModel
{
    public $order_detail_id;// 订单详情ID
    public $mall_id;

    public function rules()
    {
        return [
            [['order_detail_id'], 'required'],
            [['order_detail_id'], 'integer'],
            [['mall_id'], 'safe']
        ];
    }

    /**
     * 获取订单详情
     * @param int $from 1跳转申请售后页面2其他
     * @return array
     */
    public function getDetail($from = 2)
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $returnData = $data = [];
            $detail = OrderDetail::find()->andWhere([
                'id' => $this->order_detail_id
            ])->with('goods.goodsWarehouse', 'order')->asArray()->one();

            if (!$detail) {
                throw new \Exception('订单不存在');
            }


            $orderDetail = new OrderDetail();
            $goodsAttrInfo = $orderDetail->decodeGoodsInfo($detail['goods_info']);
            
            $goodsInfo['name'] = $detail['goods']['goodsWarehouse']['name'];
            $goodsInfo['num'] = $detail['num'];
            $goodsInfo['cannotrefund'] = $cannotrefund = json_decode($detail['goods']['cannotrefund']); //1、退款 2、退货退款 3、换货
            $goodsInfo['total_original_price'] = $detail['total_original_price'];
            $goodsInfo['member_discount_price'] = $detail['member_discount_price'];
            $goodsInfo['attr_list'] = $goodsAttrInfo['attr_list'];
            $goodsInfo['pic_url'] = $goodsAttrInfo['goods_attr']['pic_url'] ?:
                $detail['goods']['goodsWarehouse']['cover_pic'];

            
            //$cannotrefund_arr = [1=>OrderRefund::TYPE_ONLY_REFUND,2=>OrderRefund::TYPE_REFUND_RETURN,3=>OrderRefund::TYPE_EXCHANGE];
            $cannotrefund_arr = [1=>OrderRefund::TYPE_ONLY_REFUND,2=>OrderRefund::TYPE_REFUND_RETURN];
            $type_list = OrderRefund::$refund_type_array;
            $cannotrefund_str = [];
            if(is_array($cannotrefund)){
                foreach ($cannotrefund_arr as $key=>$val){
                    if(in_array($key, $cannotrefund)){
                        $cannotrefund_str[$val] = $type_list[$val];
                    }
                }
            }
            //从申请售后跳转需要显示的相关数据
            if($from == 1){

                $data_arr = (new OrderModel()) -> getOneOrderData($detail['order_id']);
                //订单为未发货，售后退运费,订单为已发货, 售后不退运费
                $realityPrice = price_format($detail['total_price']);
                //目前没有预售价，暂时设置为0
                $advance_price = 0;
                $data["refund_price"] = price_format($realityPrice + $advance_price);
                $data["refund_total_price"] = $data_arr['total_price'];
                $data["use_score"] = $data_arr['use_score'];
                $data["integral_deduction_price"] = $data_arr['integral_deduction_price'];
                $data["shopping_voucher_num"] = $data_arr['shopping_voucher_use_num'];
                $data["order_detail_id"] = $this->order_detail_id;
                $data["goods_info"] = $goodsInfo;
                $data["type_list"] = $cannotrefund_str;// OrderRefund::$refund_type_array;
                $data["refund_reason_list"] = AppConfigLogic::getRefundReasonConfig();
            }else{
                $detail['goods_info'] = $goodsInfo;
                $detail['refund_price'] = $detail['total_price'] < $detail['order']['total_pay_price'] ?
                    $detail['total_price'] : $detail['order']['total_pay_price'];
                $data = $detail;
            }

            //$detail['template_message_list'] = $this->getTemplateMessage();
            $returnData["detail"] = $data;
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'请求成功',$returnData);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }

    private function getTemplateMessage()
    {
        $arr = ['order_refund_tpl'];
        $list = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, $arr);
        return $list;
    }

    /**
     * 售后订单详情
     * @return array
     * @throws \Exception
     */
    public function getOrderRefundDetail()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        /** @var OrderRefund $orderRefund */
        $orderRefund = OrderRefund::find()->alias('o')->where([
            'o.mall_id' => $this->mall_id ?: \Yii::$app->mall->id,
            'o.order_detail_id' => $this->order_detail_id,
            'o.user_id' => \Yii::$app->user->id,
            'o.is_delete' => 0,
        ])->with('detail.goods.goodsWarehouse', 'order', 'refundAddress')->one();

        if (!$orderRefund) {
            throw new \Exception('售后订单不存在');
        }

        $newOrderRefund = ArrayHelper::toArray($orderRefund);
        $newOrderRefund['status_text'] = $orderRefund->statusText($orderRefund);
        $newOrderRefund["created_at"] = date("Y-m-d",$newOrderRefund["created_at"]);
        CommonLogic::unsetArrayKey($newOrderRefund,["updated_at","is_delete","deleted_at","is_confirm","confirm_at","customer_name","send_at","address_id"]);

        $newItem = ArrayHelper::toArray($orderRefund->detail);

        if(isset($orderRefund->detail->order)){
            $newOrderItem = ArrayHelper::toArray($orderRefund->detail->order);
            $newOrderRefund["user_address_name"] = $newOrderItem["name"];
            $newOrderRefund["user_address_mobile"] = $newOrderItem["mobile"];
            $newOrderRefund["user_address_address"] = $newOrderItem["address"];
        }

        $refundAddress = $orderRefund->refundAddress ? ArrayHelper::toArray($orderRefund->refundAddress) : [];
        $refundAddress_arr = [];
        if($refundAddress){
            $address_detail = implode(' ',json_decode($refundAddress['address'])).' '.$refundAddress['address_detail'];
            $refundAddress_arr['name'] = $refundAddress['name'];
            $refundAddress_arr['mobile'] = $refundAddress['mobile'];
            $refundAddress_arr['address'] = $address_detail;
            $refundAddress_arr['remark'] = $refundAddress['remark'];
        }
        $newOrderRefund['refund_address'] = $refundAddress_arr;



        $goodsInfo = MallGoods::getGoodsData($orderRefund->detail);
        CommonLogic::unsetArrayKey($newItem,["goods_info","updated_at","is_delete","deleted_at","created_at","back_price","sign","goods_no","form_data",
            "form_id"]);
        $newItem['goods_info'] = $goodsInfo;
        $newOrderRefund['goods_list'][] = $newItem;

        try {
            $newOrderRefund['pic_list'] = json_decode($newOrderRefund['pic_list']);
        } catch (\Exception $exception) {
            $newOrderRefund['pic_list'] = [];
        }
        $order_detail                                    = OrderDetail::findOne($newOrderRefund['order_detail_id']);
        $newOrderRefund['order_detail']['refund_status'] = $order_detail['refund_status'];
        //$newOrderRefund = array_merge($newOrderRefund, $orderRefund->checkAfterRefund($orderRefund));
        //$newOrderRefund['template_message_list'] = $this->getTemplateMessage();
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"",['detail' => $newOrderRefund]);
    }
}
