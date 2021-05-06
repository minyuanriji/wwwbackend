<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 15:47
 */


namespace app\plugins\stock\controllers\mall;

use app\core\ApiCode;
use app\plugins\stock\forms\mall\AgentOrderListForm;
use app\plugins\stock\forms\mall\GoodsPriceLogListForm;
use app\plugins\stock\forms\mall\OverListForm;
use app\plugins\stock\forms\mall\StockSettingForm;
use app\plugins\stock\forms\mall\IncomeListForm;
use app\plugins\Controller;
use app\plugins\stock\forms\mall\StockGoodsForm;
use app\plugins\stock\forms\mall\StockAgentListForm;
use app\plugins\stock\forms\mall\StockRemarksForm;
use app\plugins\stock\forms\mall\StockUserEditForm;
use app\plugins\stock\forms\mall\UpgradeBagLogListForm;
use app\plugins\stock\models\AgentOrder;
use app\plugins\stock\models\StockAgent;


class StockController extends Controller
{

    /**
     * @Author: 广东七件事 ganxiaohao
     * Date: 2020-05-10
     * Time: 21:35
     * @Note:代理商列表
     * @return string|\yii\web\Response
     * @throws \Exception
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new StockAgentListForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new StockAgentListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        }
        return $this->render('index');
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * Date: 2020-05-10
     * Time: 21:36
     * @Note:修改备注
     */
    public function actionRemarksEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new StockRemarksForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }
        }
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-11
     * @Time: 15:36
     * @Note:查找用户
     */
    public function actionSearchUser()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new StockUserEditForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getUser());
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-11
     * @Time: 15:45
     * @Note:
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new StockUserEditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        } else {
            return $this->render('edit');
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-11
     * @Time: 16:56
     * @Note:修改等级
     * @return \yii\web\Response
     */
    public function actionLevelChange()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new StockUserEditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->changeLevel());
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-11
     * @Time: 16:56
     * @Note:批量修改代理商等级
     * @return \yii\web\Response
     */
    public function actionBatchLevel()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new StockUserEditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->batchLevel());
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-10
     * @Time: 9:18
     * @Note:代理商设置
     * @return string|\yii\web\Response
     */

    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new StockSettingForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fill_sms = \Yii::$app->request->post("fill_sms");
                return $this->asJson($form->save());
            } else {
                $form = new StockSettingForm();
                return $this->asJson($form->search());
            }
        }
        return $this->render('setting');
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-10
     * @Time: 9:19
     * @Note:提成明细
     */
    public function actionIncomeList()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new IncomeListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        }
        return $this->render('income-list');
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-10
     * @Time: 9:19
     * @Note:出货收益
     */
    public function actionGoodsPriceLog()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new GoodsPriceLogListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        }
        return $this->render('goods-price-log');
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-10
     * @Time: 9:19
     * @Note:拿货越级奖励
     */
    public function actionOverList()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new OverListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        }
        return $this->render('over-list');
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-10
     * @Time: 9:19
     * @Note:拿货越级奖励
     */
    public function actionUpgradeBagLog()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new UpgradeBagLogListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        }
        return $this->render('upgrade-bag-log');
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-08-05
     * @Time: 14:49
     * @Note:自取订单
     */
    public function actionAgentOrder()
    {

        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new AgentOrderListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        }
        return $this->render('agent-order');
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-08-05
     * @Time: 16:20
     * @Note:订单发货
     */
    public function actionAgentOrderSend()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = \Yii::$app->request->post();
                $agent_order = AgentOrder::findOne(['id' => $form['order_id'], 'is_delete' => 0]);
                if ($agent_order) {
                    $agent_order->express_no = $form['express_no'];
                    $agent_order->express_name = $form['express_name'];
                    $agent_order->status = AgentOrder::STATUS_WAIT_RECEIPT;
                    if ($agent_order->save()) {
                        return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '发货成功']);
                    }
                    return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '发货失败', 'data' => ['error' => $agent_order->getErrors()]]);
                }
                return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '未知操作']);
            }
        }
        return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '未知操作']);
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-08-21
     * @Time: 20:02
     * @Note:经销商删除
     */
    public function actionDelete()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {

                $id = \Yii::$app->request->post('id');

                $agent = StockAgent::findOne(['id' => $id, 'is_delete' => 0]);
                if (!$agent) {
                    return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '该代理商不存在或者已被删除！']);
                }
                $agent->is_delete=1;
                if(!$agent->save()){
                    return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '删除失败！','error'=>$agent->getErrors()]);
                }
                return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '删除成功！']);
            }
        }
    }


}