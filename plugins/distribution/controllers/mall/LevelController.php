<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 16:10
 */

namespace app\plugins\distribution\controllers\mall;


use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\plugins\Controller;

use app\plugins\distribution\forms\common\DistributionLevelCommon;
use app\plugins\distribution\forms\mall\DistributionLevelDeleteForm;
use app\plugins\distribution\forms\mall\DistributionLevelEditForm;
use app\plugins\distribution\forms\mall\DistributionLevelEnableListForm;
use app\plugins\distribution\forms\mall\DistributionLevelListForm;
use app\plugins\distribution\forms\mall\DistributionRebuyLevelDeleteForm;
use app\plugins\distribution\forms\mall\DistributionUserEditForm;
use app\plugins\distribution\forms\mall\ExtraForm;
use app\plugins\distribution\forms\mall\RebuyLevelForm;
use app\plugins\distribution\forms\mall\SubsidyForm;
use app\plugins\distribution\forms\mall\TeamForm;
use app\plugins\distribution\models\DistributionLevel;
use app\plugins\distribution\models\DistributionSetting;
use app\plugins\distribution\models\SubsidySetting;
use app\plugins\distribution\models\Team;
use app\plugins\distribution\models\RebuyLevel;

class LevelController extends Controller
{


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-12
     * @Time: 9:52
     * @Note:等级列表
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {

            } elseif (\Yii::$app->request->isGet) {
                $form = new DistributionLevelListForm();
                $form->attributes = \Yii::$app->request->get();
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
        if (\Yii::$app->request->isAjax) {

            if (\Yii::$app->request->isPost) {

            } elseif (\Yii::$app->request->isGet) {
                $form = new DistributionLevelEnableListForm();
                return $this->asJson($form->getList());
            }

        }

    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-12
     * @Time: 9:23
     * @Note:编辑
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new DistributionLevelEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                return $this->asJson($form->save());
            } elseif (\Yii::$app->request->isGet) {
                $form = new DistributionLevelEditForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        }
        return $this->render('edit');

    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-12
     * @Time: 9:52
     * @Note:等级状态变更
     * @return \yii\web\Response
     */
    public function actionSwitchStatus()
    {
        $level = DistributionLevel::findOne(['id' => \Yii::$app->request->post('id'), 'is_delete' => 0]);
        if (!$level) {
            return $this->asJson([
                'code' => ApiCode::CODE_FAIL,
                'msg' => '该等级不存在或已被删除！',
            ]);
        }
        try {
            if ($level->is_use) {
                $level->is_use = 0;
            } else {
                $level->is_use = 1;
            }
            if ($level->save()) {
                return $this->asJson([
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '分销等级状态变更成功',
                ]);
            }
        } catch (\Exception $exception) {
            return $this->asJson([
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ]);
        }
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-12
     * @Time: 9:52
     * @Note:删除
     * @return \yii\web\Response
     */
    public function actionDelete()
    {

        if (\Yii::$app->request->isAjax) {

            if (\Yii::$app->request->isPost) {
                $form = new DistributionLevelDeleteForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-12
     * @Time: 9:52
     * @Note:删除
     * @return \yii\web\Response
     */
    public function actionRebuyDelete()
    {

        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new DistributionRebuyLevelDeleteForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }
        }
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-12
     * @Time: 9:47
     * @Note:获取分销配置以及权重
     * @return \yii\web\Response
     */
    public function actionSetting()
    {
        $level = DistributionSetting::getValueByKey(DistributionSetting::LEVEL);
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'weights' => DistributionLevelCommon::getInstance()->getLevelWeights(),
                'level' => $level
            ]
        ]);
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-20
     * @Time: 15:17
     * @Note:等级补贴
     * @return string|\yii\web\Response
     */
    public function actionSubsidyLevel()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new SubsidyForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        } else {
            return $this->render('subsidy-level');
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-20
     * @Time: 15:17
     * @Note:等级补贴
     * @return string|\yii\web\Response
     */
    public function actionSubsidyEdit()
    {
        $form = new SubsidyForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->saveSubsidy());
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-20
     * @Time: 15:17
     * @Note:等级补贴
     * @return string|\yii\web\Response
     */
    public function actionSubsidyDelete()
    {
        $subsidy = SubsidySetting::findOne(\Yii::$app->request->get('id'));
        if (!$subsidy) {
            return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '该配置不存在！']);
        }
        $subsidy->is_delete = 1;
        if(!$subsidy->save()){
            return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '删除失败！']);
        }
        return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '删除成功！']);
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-20
     * @Time: 15:17
     * @Note:复购等级
     * @return string|\yii\web\Response
     */
    public function actionRebuyLevel()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new RebuyLevelForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        } else {
            return $this->render('rebuy-level');
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-20
     * @Time: 15:17
     * @Note:复购等级
     * @return string|\yii\web\Response
     */
    public function actionRebuyLevelEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new RebuyLevelForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->saveLevel());
            }
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-20
     * @Time: 16:13
     * @Note:获取默认等级权重
     * @return \yii\web\Response
     */
    public function actionDefaultLevelWeight()
    {
        if (\Yii::$app->request->isAjax) {
            $newList = [];
            for ($i = 1; $i <= 10; $i++) {
                $newList[] = [
                    'name' => '等级' . $i,
                    'level' => $i,
                ];
            }
            return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '', 'data' => ['list' => $newList]]);
        } else {
            return false;
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-21
     * @Time: 19:11
     * @Note:配置团队奖励
     */
    public function actionTeam()
    {

        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new TeamForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        } else {
            return $this->render('team');
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-21
     * @Time: 19:11
     * @Note:配置团队奖励
     */
    public function actionTeamEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new TeamForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->saveTeam());
            }
        }
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-21
     * @Time: 19:11
     * @Note:配置团队奖励删除
     */
    public function actionExtraDelete()
    {
        $team = Team::findOne(\Yii::$app->request->post('id'));
        if (!$team) {
            return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '配置不存在！']);
        }
        $team->is_delete = 1;
        if (!$team->save()) {
            return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '删除失败！']);
        }
        return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '删除成功！']);
    }
}