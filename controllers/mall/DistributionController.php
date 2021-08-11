<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 用户管理-分销商管理
 * Author: zal
 * Date: 2020-04-16
 * Time: 10:35
 */

namespace app\controllers\mall;

use app\core\ApiCode;
use app\forms\common\distribution\DistributionLevelCommon;
use app\forms\mall\distribution\ApplyForm;
use app\forms\mall\distribution\BasicForm;
use app\forms\mall\distribution\CashApplyForm;
use app\forms\mall\distribution\CashListForm;
use app\forms\mall\distribution\ContentForm;
use app\forms\mall\distribution\EditForm;
use app\forms\mall\distribution\IndexForm;
use app\forms\mall\distribution\LevelEditForm;
use app\forms\mall\distribution\LevelForm;
use app\forms\mall\distribution\OrderForm;
use app\forms\mall\distribution\DistributionForm;
use app\forms\mall\distribution\TeamForm;
use app\forms\mall\distribution\DistributionCustomForm;
use app\models\DistributionSetting;

class DistributionController extends UserManagerController
{
    /**
     * 分销商列表
     * @return bool|string
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->post('flag') === 'EXPORT') {
            $fields = explode(',', \Yii::$app->request->post('fields'));
            $form = new IndexForm();
            $form->attributes = \Yii::$app->request->post();
            $form->fields = $fields;
            $form->getList();
            return false;
        } else {
            return $this->render('index');
        }
    }

    /**
     * 分销订单列表
     * @return bool|string|\yii\web\Response
     */
    public function actionOrder()
    {
        if (\Yii::$app->request->isAjax) {
            if (!\Yii::$app->request->isPost) {
                $form = new OrderForm();
                $get = \Yii::$app->request->get();
                $form->attributes = $get;
                if (isset($get['keyword_1']) && isset($get['keyword'])) {
                    $keyword1 = $get['keyword_1'];
                    $form->$keyword1 = $get['keyword'];
                }
                $form->mall = \Yii::$app->mall;
                return $this->asJson($form->search());
            }
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new OrderForm();
                $post = \Yii::$app->request->post();
                $form->attributes = $post;
                if (isset($post['keyword_1']) && isset($post['keyword'])) {
                    $keyword1 = $post['keyword_1'];
                    $form->$keyword1 = $post['keyword'];
                }
                $form->fields = $fields;
                $form->search();
                return false;
            } else {
                return $this->render('order');
            }
        }
    }

    /**
     * 分销商列表数据获取
     * @return \yii\web\Response
     */
    public function actionIndexData()
    {
        $form = new IndexForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    /**
     * 分销基础设置
     * @return string|\yii\web\Response
     */
    public function actionBasic()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new BasicForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new BasicForm();
                return $this->asJson($form->search());
            }
        }
        return $this->render('basic');
    }

    /**
     * 分销申请审核
     * @return \yii\web\Response
     */
    public function actionApply()
    {
        $form = new ApplyForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->save());
    }

    /**
     * 分销商删除
     * @return \yii\web\Response
     */
    public function actionDelete()
    {
        $form = new DistributionForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->delete());
    }

    /**
     * 分销商团队详情
     * @return \yii\web\Response
     */
    public function actionTeam()
    {
        $form = new TeamForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    /**
     * 添加分销商备注
     * @return \yii\web\Response
     */
    public function actionContent()
    {
        $form = new ContentForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->save());
    }

    /**
     * 分销商菜单
     * @return string|\yii\web\Response
     */
    public function actionCustomize()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new DistributionCustomForm();
            if (\Yii::$app->request->isPost) {
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->saveData());
            } else {
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getData());
            }
        } else {
            return $this->render('customize');
        }
    }

    /**
     * 提现列表页面
     * @return bool|string
     */
    public function actionCash()
    {
        if (\Yii::$app->request->post('flag') === 'EXPORT') {
            $fields = explode(',', \Yii::$app->request->post('fields'));
            $form = new CashListForm();
            $form->attributes = \Yii::$app->request->post();
            $form->attributes = \Yii::$app->request->get();
            $form->fields = $fields;
            $form->search();
            return false;
        } else {
            return $this->render('cash');
        }
    }

    /**
     * 提现列表数据
     * @return \yii\web\Response
     */
    public function actionCashData()
    {
        $form = new CashListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    /**
     * 提现申请
     * @return \yii\web\Response
     */
    public function actionCashApply()
    {
        $form = new CashApplyForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    // TODO 无作用
    public function actionOrderData()
    {
        $form = new OrderForm();
        $get = \Yii::$app->request->get();
        $form->attributes = $get;
        if (isset($get['keyword_1']) && isset($get['keyword'])) {
            $keyword1 = $get['keyword_1'];
            $form->$keyword1 = $get['keyword'];
        }
        $form->mall = \Yii::$app->mall;
        return $this->asJson($form->search());
    }

    /**
     * 二维码
     * @return \yii\web\Response
     */
    public function actionQrcode()
    {
        $form = new DistributionForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getQrcode());
    }

    /**
     * 分销等级
     * @return string|\yii\web\Response
     */
    public function actionLevel()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new LevelForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            } else {
                return $this->asJson([]);
            }
        } else {
            return $this->render('level');
        }
    }

    /**
     * 分销等级编辑
     * @return string|\yii\web\Response
     */
    public function actionLevelEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                return $this->asJson([
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '',
                    'data' => [
                        'detail' => DistributionLevelCommon::getInstance()->getDetail(\Yii::$app->request->get('id'))
                    ]
                ]);
            } else {
                $form = new LevelEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                return $this->asJson($form->save());
            }
        } else {
            return $this->render('level-edit');
        }
    }

    /**
     * 分销商配置信息
     * @return \yii\web\Response
     */
    public function actionOptions()
    {
        $level = DistributionSetting::get(\Yii::$app->mall->id, DistributionSetting::LEVEL, 0);
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => DistributionLevelCommon::getInstance()->getOptions(),
                'level' => $level
            ]
        ]);
    }

    /**
     * 分销商等级删除
     * @return \yii\web\Response
     */
    public function actionLevelDestroy()
    {
        try {
            DistributionLevelCommon::getInstance()->destroy(\Yii::$app->request->post('id'));
            return $this->asJson([
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
            ]);
        } catch (\Exception $exception) {
            return $this->asJson([
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ]);
        }
    }

    /**
     * 变更状态
     * @return \yii\web\Response
     */
    public function actionSwitchStatus()
    {
        try {
            DistributionLevelCommon::getInstance()->switchStatus(\Yii::$app->request->post('id'));
            return $this->asJson([
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '分销等级状态变更成功',
            ]);
        } catch (\Exception $exception) {
            return $this->asJson([
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ]);
        }
    }

    /**
     * 商品分销商配置
     * @return \yii\web\Response
     */
    public function actionGoodsShareConfig()
    {
        $form = new BasicForm();
        return $this->asJson($form->getGoodsDistributionConfig());
    }

    /**
     * 编辑
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new EditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        } else {
            return $this->render('edit');
        }
    }

    /**
     * 排除分销商用户
     * @return \yii\web\Response
     */
    public function actionUser()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new EditForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getUser());
        }
    }

    /**
     * 获取分销商等级
     * @return \yii\web\Response
     */
    public function actionGetLevel()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new EditForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getLevel());
        }
    }

    /**
     * 等级改变
     * @return \yii\web\Response
     */
    public function actionChangeLevel()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new EditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->changeLevel());
        }
    }

    /**
     * 批量变更等级
     * @return \yii\web\Response
     */
    public function actionBatchLevel()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new EditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->batchLevel());
        }
    }
}
