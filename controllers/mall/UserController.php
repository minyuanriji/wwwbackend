<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 用户管理-用户管理-用户中心
 * Author: zal
 * Date: 2020-04-15
 * Time: 15:35
 */

namespace app\controllers\mall;

use app\core\ApiCode;
use app\forms\common\UserRelationshipLinkForm;
use app\forms\mall\user\BalanceForm;
use app\forms\mall\user\ClerkEditForm;
use app\forms\mall\user\ClerkForm;
use app\forms\mall\user\LevelForm;
use app\forms\mall\user\RelationSettingForm;
use app\forms\mall\user\ScoreForm;
use app\forms\mall\user\UserCardForm;
use app\forms\mall\user\UserEditForm;
use app\forms\mall\user\UserForm;
use app\models\RelationSetting;

class UserController extends UserManagerController
{
    /**
     * 首页
     * @return bool|string|\yii\web\Response
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new UserForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new UserForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->getList();
                return false;
            } else {
                return $this->render('index');
            }
        }
    }

    /**
     * 编辑
     * @return array|string|\yii\web\Response
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new UserEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            } else {
                $form = new UserForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }

    /**
     * 删除
     * @return array
     */
    public function actionCouponDestroy()
    {
        if (\Yii::$app->request->isPost) {
            $form = new UserForm();
            $form->attributes = \Yii::$app->request->post();
            return $form->destroy();
        }
    }

    /**
     * @return string
     */
    public function actionHandle()
    {
        return $this->render('handle');
    }

    /**
     * 优惠券
     * @return string|\yii\web\Response
     */
    public function actionCoupon()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new UserForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getCoupon());
        } else {
            return $this->render('coupon');
        }
    }

    /**
     * 余额
     * @return array
     */
    public function actionBalance()
    {
        if (\Yii::$app->request->isPost) {
            $form = new BalanceForm();
            $form->attributes = \Yii::$app->request->post();
            return $form->save();
        }
    }

    /**
     * 余额日志
     * @return bool|string|\yii\web\Response
     */
    public function actionBalanceLog()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new UserForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->balanceLog());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new UserForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->balanceLog();
                return false;
            } else {
                return $this->render('balance-log');
            }
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-07
     * @Time: 16:33
     * @Note:充值积分
     * @return \yii\web\Response
     */
    public function actionScore()
    {
        if (\Yii::$app->request->isPost) {
            $form = new ScoreForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }

    /**
     * 积分变动日志
     * @return bool|string|\yii\web\Response
     */
    public function actionScoreLog()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new UserForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->integralLog());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new UserForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->integralLog();
                return false;
            } else {
                return $this->render('score-log');
            }
        }
    }


    /**
     * 搜索核销员
     * @return string|\yii\web\Response
     */
    public function actionClerk()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ClerkForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        } else {
            return $this->render('clerk');
        }
    }

    /**
     * 编辑核销员
     * @return \yii\web\Response
     */
    public function actionClerkEdit()
    {
        if (\Yii::$app->request->isPost) {
            $form = new ClerkEditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }

    /**
     * 删除核销员
     * @return \yii\web\Response
     */
    public function actionClerkDestroy()
    {
        if (\Yii::$app->request->isPost) {
            $form = new ClerkForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->destroy());
        }
    }

    /**
     * 核销员列表
     * @return \yii\web\Response
     */
    public function actionClerkUser()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ClerkForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->clerkUser());
        }
    }

    /**
     * 查找用户
     * @return \yii\web\Response
     */
    public function actionSearchUser()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new UserForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->searchUser());
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-03
     * @Time: 14:35
     * @Note:获取可以绑定的推荐人
     */
    public function actionGetCanBindInviter()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new UserForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getCanBindInviter());
        }
    }


    /**
     * 会员购买记录
     * @return bool|string|\yii\web\Response
     */
    public function actionLevelLog()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new LevelForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new LevelForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->search();
                return false;
            } else {
                return $this->render('level-log');
            }
        }
    }

    /**
     * 用户vip卡
     * @return string|\yii\web\Response
     */
    public function actionCard()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new UserCardForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getCard());
            }
        } else {
            return $this->render('card');
        }
    }

    /**
     * 删除用户卡
     * @return \yii\web\Response
     */
    public function actionCardDestroy()
    {
        if (\Yii::$app->request->isPost) {
            $form = new UserCardForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->destroy());
        }
    }

    /**
     * 批量删除用户卡
     * @return \yii\web\Response
     */
    public function actionCardBatchDestroy()
    {
        if (\Yii::$app->request->isPost) {
            $form = new UserCardForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->batchDestroy());
        }
    }

    /**
     * 注销
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        $logout = \Yii::$app->admin->logout();
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '注销成功',
        ]);
    }

    public function actionScoreSetting()
    {
        return $this->render('score-setting');
    }

    public function actionRelationEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new RelationSettingForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } elseif (\Yii::$app->request->isGet) {
                $form = new RelationSettingForm();
                return $this->asJson($form->getRelationSetting());
            }
        } else {
            return $this->render('relation-edit');
        }
    }

    public function actionRelationRebuild(){
        $res = UserRelationshipLinkForm::rebuild();
        if($res['code'] == ApiCode::CODE_SUCCESS){
            $res['data']['long'] = time() - $res['data']['start'];
            $res['data']['start'] = date("Y-m-d H:i:s", $res['data']['start']);
        }
        return $this->asJson($res);
    }

}
