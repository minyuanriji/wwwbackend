<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-06-29
 * Time: 16:51
 */

namespace app\plugins\agent\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\core\ApiCode;
use app\events\CommonOrderDetailEvent;
use app\handlers\CommonOrderDetailHandler;
use app\models\TestModel;
use app\plugins\agent\forms\api\TeamListForm;
use app\plugins\agent\models\AgentLevel;
use app\plugins\ApiController;
use app\plugins\agent\forms\api\AgentForm;
use app\plugins\agent\models\Agent;
use app\plugins\agent\models\AgentLevelNum;
use Yii;

class AgentController extends ApiController
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
        $form = new AgentForm();
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
        $form = new AgentForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getLogList());
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
        $list = AgentLevel::find()->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->orderBy('level ASC')->asArray()->all();
        $agent_id = Yii::$app->request->get('agent_id',0);
        $agent = Agent::getAgentByUserId(Yii::$app->user->identity->id);
        $setting = AgentLevelNum::lists(array('select'=>'id,level,num,use_num','index'=>'level','where'=>array(['agent_id' => $agent['id'] ?? 0])));
        foreach($list as $key => $level){
            $list[$key]['levelup_give_setting'] = json_decode($level['levelup_give_setting'],true);
            $list[$key]['invited_give_setting'] = json_decode($level['invited_give_setting'],true);
            $list[$key]['levelup_integral_setting'] = json_decode($level['levelup_integral_setting'],true);
        }
        return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '数据请求成成功', 'data' => compact('list','setting')]);
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
}