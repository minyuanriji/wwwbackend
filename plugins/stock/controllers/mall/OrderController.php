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
use app\models\Goods;
use app\models\User;
use app\plugins\stock\forms\mall\FillOrderListForm;
use app\plugins\stock\forms\mall\FillPriceLogListForm;
use app\plugins\Controller;
use app\plugins\stock\models\FillIncomeLog;
use app\plugins\stock\models\FillOrderDetail;
use app\plugins\stock\models\StockAgent;
use app\plugins\stock\models\StockLevel;


class OrderController extends Controller
{


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-10
     * @Time: 9:19
     * @Note:提成明细
     */
    public function actionFillPriceLog()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new FillPriceLogListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        }
        return $this->render('fill-price-log');
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-10
     * @Time: 9:19
     * @Note:提成明细
     */
    public function actionFillOrder()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new FillOrderListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        }
        return $this->render('fill-order');
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-10
     * @Time: 9:19
     * @Note:拿货订单明细
     */
    public function actionFillOrderDetail()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $order_id = \Yii::$app->request->get('order_id');
                $list = FillOrderDetail::find()->alias('d')
                    ->where(['d.order_id' => $order_id, 'd.is_delete' => 0])
                    ->select('d.*')
                    ->asArray()->all();
                foreach ($list as &$item) {
                    $goods = Goods::findOne($item['goods_id']);
                    if ($goods) {
                        $item['goods'] = $goods->goodsWarehouse;
                    }
                }
                return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '数据请求成功', 'data' => [
                    'list' => $list
                ]]);
            }
        }
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-31
     * @Time: 9:26
     * @Note: 佣金详情
     * @return \yii\web\Response
     */
    public function actionFillPriceDetail()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $log_id = \Yii::$app->request->get('log_id');
                $list = FillIncomeLog::find()->alias('l')
                    ->leftJoin(['u' => User::tableName()], 'u.id=l.user_id')
                    ->where(['l.fill_price_log_id' => $log_id, 'l.is_delete' => 0])
                    ->select('l.*,u.nickname,u.avatar_url,u.id as user_id')
                    ->asArray()
                    ->all();
                foreach ($list as &$item) {
                    $item['created_at']=date('Y-m-d H:i:s',$item['created_at']);
                    $agent = StockAgent::findOne(['user_id' => $item['user_id']]);
                    $item['user_level_name'] = '默认等级';
                    if ($agent) {
                        $level = StockLevel::findOne(['level' => $agent->level, 'mall_id' => $agent->mall_id, 'is_delete' => 0]);
                        if ($level) {
                            $item['user_level_name'] = $level->name;
                        }
                    }
                }
                return $this->asJson([
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '数据请求成功',
                    'data' => ['list' => $list]
                ]);
            }
        }

    }

}