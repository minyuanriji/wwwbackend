<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 16:10
 */

namespace app\plugins\agent\controllers\mall;


use app\core\ApiCode;
use app\logic\IntegralLogic;
use app\models\Order;
use app\plugins\Controller;

use app\plugins\agent\forms\common\AgentLevelCommon;
use app\plugins\agent\forms\mall\AgentLevelDeleteForm;
use app\plugins\agent\forms\mall\AgentLevelEditForm;
use app\plugins\agent\forms\mall\AgentLevelEnableListForm;
use app\plugins\agent\forms\mall\AgentLevelListForm;
use app\plugins\agent\models\Agent;
use app\plugins\agent\models\AgentLevel;
use app\plugins\agent\models\AgentLevelNum;
use Yii;

class LevelController extends Controller
{


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-12
     * @Time: 9:52
     * @Note:等级列表
     * @return string|yii\web\Response
     */
    public function actionIndex()
    {
        if (Yii::$app->request->isAjax) {
            if (Yii::$app->request->isPost) {

            } elseif (Yii::$app->request->isGet) {
                $form = new AgentLevelListForm();
                $form->attributes = Yii::$app->request->get();
                return $this->asJson($form->search());
            }

        }

        return $this->render('index');
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * Date: 2020-05-10
     * Time: 22:26
     * @Note:已经启用的等级
     */
    public function actionEnableList()
    {
        if (Yii::$app->request->isAjax) {

            if (Yii::$app->request->isPost) {

            } elseif (Yii::$app->request->isGet) {
                $form = new AgentLevelEnableListForm();
                $form->level = Yii::$app->request->get('level');
                return $this->asJson($form->getList());
            }

        }

    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-12
     * @Time: 9:23
     * @Note:编辑
     * @return string|yii\web\Response
     */
    public function actionEdit()
    {
        if (Yii::$app->request->isAjax) {
            if (Yii::$app->request->isPost) {
                $res = AgentLevel::setData(Yii::$app->request->post('form'));
                if($res === false) return $this->error(AgentLevel::getError());
                return $this->success();
            } elseif (Yii::$app->request->isGet) {
                $id =Yii::$app->request->get('id');
                $detail = AgentLevel::getDetail($id);
                $weights = AgentLevelCommon::getInstance(\Yii::$app->mall)->getLevelWeights();
                $levels = AgentLevel::lists(array(
                    'select'=>'id,level,name',
                    'where'=>array(['is_delete' => 0])
                ));
                return $this->success('success',compact('detail','weights','levels'));
            }
        }
        return $this->render('edit');
    }

    /**
     * 名额赠送设置
     * @Author bing
     * @DateTime 2020-10-28 11:41:04
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public function actionLevelNumSetting(){
        if (Yii::$app->request->isAjax) {
            if (Yii::$app->request->isPost) {
                $res = AgentLevel::levelNumSetting(Yii::$app->request->post('form'));
                if($res === false) return $this->error(AgentLevel::getError());
                return $this->success();
            } elseif (Yii::$app->request->isGet) {
                $id =Yii::$app->request->get('id',0);
                if($id < 1) return $this->error('id参数错误');
                $setting = AgentLevel::getLevelNumSetting($id) ?? [];
                if($setting === false) return $this->error(AgentLevel::getError());
                $levels = AgentLevel::lists(array(
                    'select'=>'id,level,name',
                    'where'=>array(['is_delete' => 0])
                ));
                return $this->success('success',compact('setting','levels'));
            }
        }
        return $this->render('level-num-setting');
    }

     /**
     * 名额赠送设置
     * @Author bing
     * @DateTime 2020-10-28 11:41:04
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public function actionLevelup(){
        $agent = Agent::getOne(array('id'=>1));
        $res = AgentLevelNum::increaseNum($agent,AgentLevelNum::SCENE_INVITED,1);
        var_dump($res,AgentLevelNum::getError());die;
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-12
     * @Time: 9:52
     * @Note:等级状态变更
     * @return yii\web\Response
     */
    public function actionSwitchStatus()
    {
        $level = AgentLevel::findOne(array('id' => Yii::$app->request->post('id'), 'is_delete' => 0));
        if (empty($level)) return $this->error('该等级不存在或已被删除！');
        $level->is_use =  $level->is_use == 0 ? 1: 0;
        $res = $level->save();
        if($res === false) return $this->error($level->getErrorMessage());
        return $this->success('分销等级状态变更成功');
    
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-12
     * @Time: 9:52
     * @Note:删除
     * @return yii\web\Response
     */
    public function actionDelete()
    {

        if (Yii::$app->request->isAjax) {

            if (Yii::$app->request->isPost) {
                $form = new AgentLevelDeleteForm();
                $form->attributes = Yii::$app->request->post();
                return $this->asJson($form->save());
            }
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-12
     * @Time: 9:47
     * @Note:获取分销配置以及权重
     * @return yii\web\Response
     */
    public function actionSetting()
    {

        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'weights' => AgentLevelCommon::getInstance()->getLevelWeights(),

            ]
        ]);
    }

    
    public function actionTest(){
        // $level_info = AgentLevel::findOne(1);
        // $agent = Agent::findOne(array('user_id'=>17));
        // $res = IntegralLogic::levelupSendIntegral($level_info,$agent);
        // var_dump($res);die;

        $order_id = 37;
        $order = Order::findOne($order_id);
        $integralLogic = new IntegralLogic();
        $integralLogic->refundIntegral($order);
    }
}