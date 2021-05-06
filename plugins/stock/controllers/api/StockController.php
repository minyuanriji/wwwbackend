<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-06-29
 * Time: 16:51
 */

namespace app\plugins\stock\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\core\ApiCode;
use app\events\CommonOrderDetailEvent;
use app\handlers\CommonOrderDetailHandler;
use app\helpers\SerializeHelper;
use app\logic\AppConfigLogic;
use app\models\Goods;
use app\models\PaymentOrderUnion;
use app\models\TestModel;
use app\plugins\ApiController;
use app\plugins\stock\forms\api\AgentGoodsListForm;
use app\plugins\stock\forms\api\AgentOrderListForm;
use app\plugins\stock\forms\api\AgentOrderSubmitForm;
use app\plugins\stock\forms\api\BuyGoodsPriceListForm;
use app\plugins\stock\forms\api\FillIncomeLogListForm;
use app\plugins\stock\forms\api\FillOrderPayDataForm;
use app\plugins\stock\forms\api\FillOrderSubmitForm;
use app\plugins\stock\forms\api\GoodsPriceLogListForm;
use app\plugins\stock\forms\api\StockForm;
use app\plugins\stock\forms\api\TeamListForm;
use app\plugins\stock\models\AgentOrder;
use app\plugins\stock\models\StockAgent;
use app\plugins\stock\models\StockAgentGoods;
use app\plugins\stock\models\StockGoods;
use app\plugins\stock\models\StockLevel;

class StockController extends ApiController
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
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-06-29
     * @Time: 16:58
     * @Note:分销中心
     */

    public function actionInfo()
    {
        $form = new StockForm();
        return $this->asJson($form->getInfo());
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-06-29
     * @Time: 17:42
     * @Note:分销日志
     * @return \yii\web\Response
     */
    public function actionLogList()
    {
        $form = new StockForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getLogList());
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-08-03
     * @Time: 16:53
     * @Note:获取支付方式
     */
    public function actionPayment()
    {
        $payment_list = [];
        $payment = AppConfigLogic::getPaymentConfig(\Yii::$app->mall->id);
        if ($payment['wechat_status'] == 1) {
            $item['payment_name'] = '微信支付';
            $item['payment_type'] = 'WECHAT_PAY';
            $payment_list[] = $item;
        }
        if ($payment['balance_status'] == 1) {
            $item['payment_name'] = '余额支付';
            $item['payment_type'] = 'BALANCE_PAY';
            $payment_list[] = $item;
        }
        return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '', 'data' => ['payment' => $payment_list]]);
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-08-03
     * @Time: 11:10
     * @Note:提交补货订单
     * @return \yii\web\Response
     */
    public function actionFillOrderSubmit()
    {
        $form = new FillOrderSubmitForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->save());
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-08-03
     * @Time: 16:22
     * @Note:
     */
    public function actionFillOrderPayData()
    {
        $form = new FillOrderPayDataForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->payData());
    }



    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-08-03
     * @Time: 11:07
     * @Note:货款收益
     */
    public function actionGoodsPriceLog()
    {
        $form = new GoodsPriceLogListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-08-03
     * @Time: 14:12
     * @Note:订货奖励
     * @return \yii\web\Response
     */
    public function actionBuyGoodsPrice()
    {
        $form = new BuyGoodsPriceListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-08-03
     * @Time: 14:12
     * @Note: 平级奖相关
     * @return \yii\web\Response
     */
    public function actionFillPriceLog()
    {
        $form = new FillIncomeLogListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-08-03
     * @Time: 14:12
     * @Note:获取团队列表
     */
    public function actionTeamList()
    {
        $form = new TeamListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-08-03
     * @Time: 14:28
     * @Note:所有的等级列表
     * @return \yii\web\Response
     */
    public function actionLevelList()
    {
        $level_list = StockLevel::find()->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->orderBy('level ASC')->asArray()->all();
        return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '数据请求成成功', 'data' => ['list' => $level_list]]);
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-08-03
     * @Time: 14:29
     * @Note: 代理商的库存商品
     */
    public function actionAgentGoods()
    {
        $form = new AgentGoodsListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-08-03
     * @Time: 15:04
     * @Note:代理商提交自用订单
     */
    public function actionAgentOrderSubmit()
    {
        $form = new AgentOrderSubmitForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->save());
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-08-03
     * @Time: 15:37
     * @Note: 代理商订单列表
     */
    public function actionAgentOrderList()
    {
        $form = new AgentOrderListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

    public function actionStockGoodsList()
    {
        $agent = StockAgent::findOne(['user_id' => \Yii::$app->user->id, 'is_delete' => 0]);
        if (!$agent) {
            return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '你不是代理商']);
        }

        $list = StockGoods::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->asArray()->all();

        foreach ($list as &$item) {

            if ($item['agent_price']) {
                $agent_price = SerializeHelper::decode($item['agent_price']);
                foreach ($agent_price as $price) {
                    if ($price['level'] == $agent->level) {
                        $item['price'] = $price['stock_price'];
                    }
                }
                unset($price);
            }
            $goods = Goods::findOne($item['goods_id']);
            if ($goods) {
                $goodsInfo = $goods->goodsWarehouse;
                $item['cover_pic'] = $goodsInfo->cover_pic;
                $item['goods_name'] = $goodsInfo->name;
            }
            $agentGoods = StockAgentGoods::findOne(['goods_id' => $item['goods_id'], 'user_id' => \Yii::$app->user->id, 'is_delete' => 0]);
            $item['stock_num'] = 0;
            if ($agentGoods) {
                $item['stock_num'] = $agentGoods->num;
            }
            $item['buy_num'] = 0;
        }
        unset($item);
        return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '请求成功', 'data' => ['list' => $list]]);
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-08-05
     * @Time: 14:24
     * @Note:代理商自己的订单确认收货事件
     */
    public function actionOrderConfirm()
    {
        $agentOrder = AgentOrder::findOne(['id' => \Yii::$app->request->get('order_id'), 'is_delete' => 0]);
        if (!$agentOrder) {
            return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '未找到该订单']);
        }
        $agentOrder->status = AgentOrder::STATUS_COMPLETE;
        if($agentOrder->save()){
            return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '未找到该订单']);
        }

        return $this->asJson(['code' =>ApiCode::CODE_FAIL, 'msg' => '操作失败','data'=>[
            'error'=>$agentOrder->getErrors()
        ]]);
    }

}