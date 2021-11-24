<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单接口类
 * Author: zal
 * Date: 2020-04-29
 * Time: 14:01
 */

namespace app\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\core\ApiCode;
use app\forms\api\order\ConsumeVerificationInfoForm;
use app\forms\api\order\OrderClerkLogForm;
use app\forms\api\order\OrderCommentForm;
use app\forms\api\order\OrderClerkForm;
use app\forms\api\order\OrderDetailForm;
use app\forms\api\order\OrderEditForm;
use app\forms\api\order\OrderExpressForm;
use app\forms\api\order\OrderExtendedTimeForm;
use app\forms\api\order\OrderForm;
use app\forms\api\order\OrderListPayForm;
use app\forms\api\order\OrderPayForm;
use app\forms\api\order\OrderRefundForm;
use app\forms\api\order\OrderRefundSendForm;
use app\forms\api\order\OrderRefundSubmitForm;
use app\forms\api\order\OrderPayResultForm;
use app\forms\api\order\OrderSubmitForm;
use app\forms\api\order\QueryClerkStatusForm;
use app\logic\OrderLogic;
use app\models\Express;
use app\controllers\business\{PostageRules,OrderCommon};
use app\forms\mall\order\OrderForm as OrderFormMall;
class OrderController extends ApiController
{

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    /**
     * 预览
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 15:33
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\db\Exception
     */
    public function actionToSubmitOrder()
    {
        $form = new OrderSubmitForm();
        $form->form_data = $this->requestData;
        $result = $form->toSubmitOrder();
        return $this->asJson($result);
    }
    
     public function actionGetFlag(){
        $data = $this->requestData;
        $result = (new OrderCommon()) -> getOneNavData($data['nav_id']);
        return $this -> asJson($result);

    }
    

    /**
     * 可用优惠券列表
     * @Author: zal
     * @Date: 2020-05-04
     * @Time: 15:33
     * @return array
     * @throws \app\core\exceptions\OrderException
     */
    public function actionUsableCouponList()
    {
        $form = new OrderSubmitForm();
        $form_data = $this->requestData;
        $list = $form->getUsableCouponList($form_data);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
            ],
        ];
    }

    /**
     * 提交订单
     * @Author: zal
     * @Date: 2020-05-04
     * @Time: 15:33
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionDoSubmitOrder()
    {
        $form = new OrderSubmitForm();
        $form->form_data = $this->requestData;
        $mallPaymentTypes = OrderLogic::getPaymentTypeConfig();
        $headers = \Yii::$app->request->headers;
        if(isset($headers["x-stands-mall-id"]) && !empty($headers["x-stands-mall-id"]) && $headers["x-stands-mall-id"] != 5){
            $form->mall_id = $headers["x-stands-mall-id"];
        }else{
            $form->mall_id = \Yii::$app->mall->id;
        }
        return $this->asJson($form->setSupportPayTypes($mallPaymentTypes)->doSubmitOrder());
    }
    
    //切换地址时获取快递价格
     public function actionGetExpressPrice()
    {
        $data = $this->requestData;
        $result = (new PostageRules()) -> getExpressPrice($data);
        return $this->asJson($result);
    }
    

    /**
     * 去支付
     * Author: zal
     * @Date: 2020-05-07
     * @Time: 09:33
     * @return array
     */
    public function actionToPay()
    {
        $form = new OrderPayForm();
        $form->attributes = $this->requestData;
        return $form->loadPayData();
    }

    /**
     * 订单列表
     * Author: zal
     * @Date: 2020-05-08
     * @Time: 20:33
     * @return array
     * @throws \Exception
     */
    public function actionList()
    {
        $form = new OrderForm();
        $form->attributes = $this->requestData;
        return $form->getOrderList();
    }

    /**
     * 订单售后列表
     * Author: zal
     * @Date: 2020-05-08
     * @Time: 20:33
     * @return array
     * @throws \Exception
     */
    public function actionRefundList()
    {
        $form = new OrderForm();
        $form->attributes = $this->requestData;
        return $form->getRefundOrderList();
    }

    /**
     * 订单售后删除记录
     * @return array
     * @throws \Exception
     */
    public function actionDelRefundOrder()
    {
        $form = new OrderForm();
        $form->attributes = $this->requestData;
        return $form->DelRefundOrder();
    }


    /**
     * 订单详情
     * Author: zal
     * @Date: 2020-05-11
     * @Time: 10:33
     * @return \yii\web\Response
     */
    public function actionDetail()
    {
        $form = new OrderDetailForm();
        $form->attributes = $this->requestData;
        return $form->getDetail();
    }

    public function actionPayResult()
    {
        $form = new OrderPayResultForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getResponseData());
    }


    /**
     * 加载提交售后订单数据
     * @return array
     */
    public function actionToRefundSubmit()
    {
        $form = new OrderRefundForm();
        $form->attributes = $this->requestData;
        return $form->getDetail(1);
    }

    /**
     * 提交售后订单
     * @return array
     */
    public function actionDoRefundSubmit()
    {
        $form = new OrderRefundSubmitForm();
        $form->pic_list = isset($this->requestData["pic_list"])?json_encode($this->requestData["pic_list"]):"";
        unset($this->requestData["pic_list"]);
        $form->attributes = $this->requestData;
        return $form->submit();
    }

    /**
     * 售后 退换货用户 发货
     * @return \yii\web\Response
     */
    public function actionRefundSend()
    {
        $form = new OrderRefundSendForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->send());
    }

    /**
     * 售后订单详情
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionRefundDetail()
    {
        $form = new OrderRefundForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getOrderRefundDetail());
    }

    /**
     * 订单确认收货
     * @return \yii\web\Response
     */
    public function actionConfirm()
    {
        $form = new OrderEditForm();
        $form->attributes = $this->requestData;
        return $form->orderConfirm();
    }
    

    
    
    //用户确认收货触发，返回积分
    public function actionOrderSales(){
        $form = new OrderFormMall();
        $form->attributes = $this->requestData;
        return $this->asJson($form->orderSales());
    }

    /**
     * 订单取消 | 申请取消退款
     * @return \yii\web\Response
     */
    public function actionCancel()
    {
        $form = new OrderEditForm();
        $form->attributes = $this->requestData;
        return $form->orderCancel();
    }

    /**
     * 订单列表 未付款订单支付
     * Author: zal
     * @Date: 2020-05-11
     * @Time: 20:33
     * @return array
     */
    public function actionListPayData()
    {
        $form = new OrderListPayForm();
        $form->attributes = $this->requestData;
        return $form->loadPayData();
    }

    /**
     * 订单评价
     * Author: zal
     * @Date: 2020-05-11
     * @Time: 20:33
     * @return \yii\web\Response
     */
    public function actionComment()
    {
        $form = new  OrderCommentForm();
        $form->attributes = $this->requestData;
        return $form->comment();
    }

    /**
     * 订单删除
     * @return array
     */
    public function actionDelete()
    {
        $form = new OrderEditForm();
        $form->attributes = $this->requestData;
        return $form->orderDelete();
    }

    /**
     * 提醒发货
     * @return array
     */
    public function actionRemindSend()
    {
        $form = new OrderEditForm();
        $form->attributes = $this->requestData;
        return $form->remindSend();
    }


    /**
     * 订单物流详情
     * @return \yii\web\Response
     */
    public function actionExpressDetail()
    {
        $form = new OrderExpressForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    /**
     * 订单核销确认收款
     */
    public function actionClerkAffirmPay()
    {
        $form = new OrderClerkForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->affirmPay();
    }

    /**
     * 订单核销
     */
    public function actionOrderClerk()
    {
        $form = new OrderClerkForm();
        $form->attributes  = $this->requestData;
        $form->action_type = 1;

        return $this->asJson($form->OrderClerk());
    }

    /**
     * 查询订单核销记录
     */
    public function actionOrderClerkLog()
    {
        $form = new OrderClerkLogForm();
        $form->attributes  = $this->requestData;

        return $this->asJson($form->get());
    }

    /**
     * 查询核销状态
     */
    public function actionQueryClerkStatus(){
        $form = new QueryClerkStatusForm();
        $form->attributes  = $this->requestData;
        return $this->asJson($form->queryClerk());
    }

    /**
     * 核销码
     * @return \yii\web\Response
     */
    public function actionClerkQrCode()
    {
        $form = new OrderClerkForm();
        $form->attributes = $this->requestData;

        return $this->asJson($form->qrClerkCode());
    }

    public function actionExpressList()
    {
        $list = Express::getExpressList();

        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list
            ]
        ]);
    }

    public function actionStoreList($longitude = null, $latitude = null)
    {
        $form = new StoreForm();
        $form->attributes = \Yii::$app->request->get();
        $form->limit = 30;
        $result = $form->search();
        if (!$longitude || !$latitude) {
            return $result;
        }
        if ($result['code'] == 0 && isset($result['data']) && isset($result['data']['list'])) {
            foreach ($result['data']['list'] as &$store) {
                if (!$store['longitude'] || !$store['latitude']) {
                    $store['distance'] = '-';
                    continue;
                }
                $distance = get_distance($longitude, $latitude, $store['longitude'], $store['latitude']);
                if (is_nan($distance)) {
                    continue;
                }
                if ($distance >= 1000) {
                    $distance = round($distance / 1000, 2) . 'km';
                } else {
                    $distance = round($distance, 2) . 'm';
                }
                $store['distance'] = $distance;
            }
            return $result;
        } else {
            return $result;
        }
    }

    /**
     * 到店消费核销码二维码
     * @return \yii\web\Response
     */
    public function actionConsumeVerificationQrcode(){
        $form = new ConsumeVerificationInfoForm();
        $form->attributes = $this->requestData;

        return $this->asJson($form->qrCode());
    }

    /**
     * 到店消费核销码信息
     * @return \yii\web\Response
     */
    public function actionConsumeVerificationInfo(){
        $form = new ConsumeVerificationInfoForm();
        $form->attributes = $this->requestData;

        return $this->asJson($form->info());
    }

    /**
     * 订单延长收货时间
     * 只能延长一次，7天
     * @return \yii\web\Response
     */
    public function actionOrderExtendedReceivingTime()
    {
        $form = new OrderExtendedTimeForm();
        $form->attributes = $this->requestData;

        return $this->asJson($form->extended());
    }
}
