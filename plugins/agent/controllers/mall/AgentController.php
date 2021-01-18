<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 15:47
 */


namespace app\plugins\agent\controllers\mall;

use app\core\ApiCode;
use app\plugins\agent\forms\mall\AgentSettingForm;
use app\plugins\agent\forms\mall\IncomeListForm;
use app\plugins\agent\models\Agent;
use app\plugins\Controller;
use app\plugins\agent\forms\mall\AgentGoodsForm;
use app\plugins\agent\forms\mall\AgentListForm;
use app\plugins\agent\forms\mall\AgentRemarksForm;
use app\plugins\agent\forms\mall\AgentUserEditForm;
use app\plugins\agent\models\AgentLevel;
use app\plugins\agent\models\AgentLevelNum;
use Yii;

class AgentController extends Controller
{

    /**
     * @Author: 广东七件事 ganxiaohao
     * Date: 2020-05-10
     * Time: 21:35
     * @Note:经销商列表
     * @return string|yii\web\Response
     * @throws \Exception
     */
    public function actionIndex()
    {
        if (Yii::$app->request->isAjax) {
            if (Yii::$app->request->isPost) {
                $form = new AgentListForm();
                $form->attributes = Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new AgentListForm();
                $form->attributes = Yii::$app->request->get();
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
        if (Yii::$app->request->isAjax) {
            if (Yii::$app->request->isPost) {
                $form = new AgentRemarksForm();
                $form->attributes = Yii::$app->request->post();
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
        if (Yii::$app->request->isAjax) {
            $form = new AgentUserEditForm();
            $form->attributes = Yii::$app->request->get();
            return $this->asJson($form->getUser());
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-11
     * @Time: 15:45
     * @Note:
     * @return string|yii\web\Response
     */
    public function actionEdit()
    {
        if (Yii::$app->request->isAjax) {
            $res = Agent::setData(Yii::$app->request->post());
            if($res === false) return $this->error(Agent::getError());
            return $this->success();
            // $form = new AgentUserEditForm();
            // $form->attributes = Yii::$app->request->post();
            // return $this->asJson($form->save());
        } else {
            return $this->render('edit');
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-11
     * @Time: 16:56
     * @Note:修改等级
     * @return yii\web\Response
     */
    public function actionLevelChange()
    {
        if (Yii::$app->request->isAjax) {
            $form = new AgentUserEditForm();
            $form->attributes = Yii::$app->request->post();
            return $this->asJson($form->changeLevel());
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-11
     * @Time: 16:56
     * @Note:批量修改经销商等级
     * @return yii\web\Response
     */
    public function actionBatchLevel()
    {
        if (Yii::$app->request->isAjax) {
            $form = new AgentUserEditForm();
            $form->attributes = Yii::$app->request->post();
            return $this->asJson($form->batchLevel());
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-10
     * @Time: 9:18
     * @Note:经销商设置
     * @return string|yii\web\Response
     */

    public function actionSetting()
    {
        if (Yii::$app->request->isAjax) {
            if (Yii::$app->request->isPost) {
                $form = new AgentSettingForm();
                $form->attributes = Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new AgentSettingForm();
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
        if (Yii::$app->request->isAjax) {
            if (Yii::$app->request->isGet) {
                $form = new IncomeListForm();
                $form->attributes = Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        }
        return $this->render('income-list');

    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-08-21
     * @Time: 20:02
     * @Note:经销商删除
     */
    public function actionDelete()
    {
        if (Yii::$app->request->isAjax) {
            if (Yii::$app->request->isPost) {

                $id = Yii::$app->request->post('id');

                $agent = Agent::findOne(['id' => $id, 'is_delete' => 0]);
                if (!$agent) {
                    return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '该经销商不存在或者已被删除！']);
                }
                $agent->is_delete=1;
              if(!$agent->save()){
                  return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '删除失败！','error'=>$agent->getErrors()]);
              }
                return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '删除成功！']);
            }
        }
    }

    /**
     * 设置经销商名额数量
     * @Author bing
     * @DateTime 2020-10-29 09:27:04
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public function actionSetLevelNum(){
        if (Yii::$app->request->isAjax) {
            if (Yii::$app->request->isPost) {
                $form = Yii::$app->request->post('form');
                $agent_id = $form['agent_id'] ?? 0;
                $setting =  $form['setting'] ?? [];
                $res = AgentLevelNum::setAgentLevelNum($agent_id,$setting);
                if($res === false) return $this->error(AgentLevelNum::getError());
                return $this->success();
            }else{
                $agent_id = Yii::$app->request->get('agent_id',0);
                $setting = AgentLevelNum::lists(array('select'=>'id,level,num,use_num','where'=>array(['agent_id' => $agent_id])));
                $levels = AgentLevel::lists(array('select'=>'id,level,name','where'=>array(['is_delete' => 0])));
                return $this->success('success',compact('setting','levels'));
            }
        }
    }

}