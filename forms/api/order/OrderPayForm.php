<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单api-订单支付页数据
 * Author: zal
 * Date: 2020-04-21
 * Time: 14:50
 */

namespace app\forms\api\order;

use app\core\ApiCode;
use app\forms\common\order\OrderCommon;
use app\models\Order;
use app\models\User;

class OrderPayForm extends OrderPayFormBase
{
    public $queue_id = 0;
    public $token;
    public $id = 0;

    public function rules()
    {
        return [
            [['token'], 'string'],
            [['queue_id', 'id'], 'integer'],
        ];
    }


    /**
     * 加载支付数据
     * @return array
     */
    public function loadPayData()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"",$this);
        }
//        if(empty(\Yii::$app->user->identity->mobile)){
//            return $this->returnApiResultData(ApiCode::CODE_FAIL,"请先绑定手机号");
//        }
        if (!OrderCommon::checkIsBindMobile()) {
            return $this->returnApiResultData(ApiCode::CODE_BIND_MOBILE, '请先绑定手机号');
        }
       
        //有id值说明从订单列表跳转支付页
        if(!empty($this->id)){
            /** @var Order[] $orders */
            $orders = Order::getOrderInfo([
                'id' => $this->id,
                'is_delete' => 0,
                'user_id' => \Yii::$app->user->id,
            ]);
        }else{
            /** @var Order[] $orders */
            $orders = Order::findAll([
                'token'     => $this->token,
                'is_delete' => 0,
                'user_id'   => \Yii::$app->user->id,
            ]);
        }
        if (empty($orders)) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"订单不存在或已失效");
        }
        $userModel = new User();
        $userData = $userModel->findIdentity(\Yii::$app->user->id);
        return $this->loadOrderPayData($orders, $userData);
    }
}
