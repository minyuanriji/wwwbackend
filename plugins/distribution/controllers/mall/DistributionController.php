<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 15:47
 */


namespace app\plugins\distribution\controllers\mall;

use app\core\ApiCode;
use app\plugins\Controller;
use app\plugins\distribution\forms\mall\ApplyListForm;
use app\plugins\distribution\forms\mall\DistributionGoodsForm;
use app\plugins\distribution\forms\mall\DistributionListForm;
use app\plugins\distribution\forms\mall\DistributionRemarksForm;
use app\plugins\distribution\forms\mall\DistributionUserEditForm;
use app\plugins\distribution\forms\mall\IncomeListForm;
use app\plugins\distribution\forms\mall\RebuyPriceListForm;
use app\plugins\distribution\forms\mall\SubsidyPriceListForm;
use app\plugins\distribution\forms\mall\TeamPriceListForm;
use app\plugins\distribution\models\Distribution;
use app\plugins\distribution\models\DistributionApply;


class DistributionController extends Controller
{

    /**
     * @Author: 广东七件事 ganxiaohao
     * Date: 2020-05-10
     * Time: 21:35
     * @Note:分销商列表
     * @return string|\yii\web\Response
     * @throws \Exception
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new DistributionListForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new DistributionListForm();
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
                $form = new DistributionRemarksForm();
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
            $form = new DistributionUserEditForm();
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
            $form = new DistributionUserEditForm();
            $form->attributes = \Yii::$app->request->post();
            $form->apply_status = DistributionApply::STATUS_PASS;
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
            $form = new DistributionUserEditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->changeLevel());
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-11
     * @Time: 16:56
     * @Note:批量修改经销商等级
     * @return \yii\web\Response
     */
    public function actionBatchLevel()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new DistributionUserEditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->batchLevel());
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-19
     * @Time: 16:54
     * @Note:商品单独分销设置
     *
     */
    public function actionDistributionConfig()
    {
        $form = new DistributionGoodsForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getDistributionConfig());
    }


    /**
     * 商品分销设置
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-21
     * @Time: 19:29
     * @return \yii\web\Response
     */
    public function actionGoodsDistributionSetting()
    {
        $form = new DistributionGoodsForm();
        $form->attributes = \Yii::$app->request->post('form');
        return $this->asJson($form->setDistributionGoodsSetting());
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-21
     * @Time: 17:19
     * @Note:复购提成记录
     */
    public function actionRebuyPriceList()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new RebuyPriceListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        }
        return $this->render('rebuy-price');
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
     * @Date: 2020-07-22
     * @Time: 11:29
     * @Note:补贴记录
     * @return string|\yii\web\Response
     */
    public function actionSubsidyList()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new SubsidyPriceListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        }
        return $this->render('subsidy-list');
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-22
     * @Time: 11:29
     * @Note:团队记录
     * @return string|\yii\web\Response
     */
    public function actionTeamList()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new TeamPriceListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        }
        return $this->render('team-list');
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

                $agent = Distribution::findOne(['id' => $id, 'is_delete' => 0]);
                if (!$agent) {
                    return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '该分销商不存在或者已被删除！']);
                }
                $agent->is_delete=1;
                if(!$agent->save()){
                    return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '删除失败！','error'=>$agent->getErrors()]);
                }
                /** @var DistributionApply $apply */
                $apply = DistributionApply::find()->where(["user_id" => $agent->user_id,"is_delete" => DistributionApply::IS_DELETE_NO])->one();
                if(!empty($apply)){
                    $apply->is_delete = DistributionApply::IS_DELETE_YES;
                    $apply->save();
                }
                return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '删除成功！']);
            }
        }
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-22
     * @Time: 16:08
     * @Note:申请列表
     */
    public function actionApply()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new ApplyListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->search());
            }
        }
        return $this->render('apply-list');
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-22
     * @Time: 16:08
     * @Note:申请列表
     */
    public function actionApplyDelete()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $apply_id = \Yii::$app->request->post('id');
                $apply = DistributionApply::findOne($apply_id);
                if (!$apply) {
                    return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '申请不存在！']);
                }
                $apply->is_delete = 1;
                if (!$apply->save()) {
                    return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '删除失败！']);
                }
                return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '删除成功！']);
            }
        }

    }

}