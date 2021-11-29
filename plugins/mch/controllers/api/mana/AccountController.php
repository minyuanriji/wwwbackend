<?php
namespace app\plugins\mch\controllers\api\mana;

use app\mch\forms\api\mch_set\SetAccountForm;
use app\plugins\mch\forms\api\mana\MchManaAccountIncomeLogForm;
use app\plugins\mch\forms\api\mana\MchManaAccountInfoForm;
use app\plugins\mch\forms\api\mana\MchManaAccountLogForm;
use app\plugins\mch\forms\api\mana\MchManaAccountSetPwdForm;
use app\plugins\mch\forms\api\mana\MchManaAccountSetSettleInfo;
use app\plugins\mch\forms\api\mana\MchManaAccountUpdateMobileForm;
use app\plugins\mch\forms\api\mana\MchManaAccountValidateMobileForm;
use app\plugins\mch\forms\api\mana\MchManaAccountWithdrawForm;
use app\plugins\mch\forms\api\mana\MchManaAccountWithdrawLogForm;
use app\plugins\mch\forms\api\mana\MchManaAccountSetWithdrawPwdForm;

class AccountController extends MchAdminController {

    /**
     * 设置账号密码
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionSetPwd(){
        /*$form = new MchManaAccountSetPwdForm();
        $form->attributes = $this->requestData;
        return $form->save();*/

        $form = new SetAccountForm();
        $form->attributes = $this->requestData;
        $form->mch_id     = MchAdminController::$adminUser['mch_id'];
        $form->mall_id    = MchAdminController::$adminUser['mall_id'];
        $this->asJson($form->save());
    }

    /**
     * 验证绑定手机
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionValidateMobile(){
        $form = new MchManaAccountValidateMobileForm();
        $form->attributes = $this->requestData;
        $this->asJson($form->check());
    }

    /**
     * 更新绑定手机
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionUpdateMobile(){
        $form = new MchManaAccountUpdateMobileForm();
        $form->attributes = $this->requestData;
        $this->asJson($form->update());
    }

    /**
     * 设置提现密码
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionSetWithdrawPwd(){
        $form = new MchManaAccountSetWithdrawPwdForm();
        $form->attributes = $this->requestData;
        $this->asJson($form->save());
    }

    /**
     * 设置结算信息
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionSetSettleInfo(){
        $form = new MchManaAccountSetSettleInfo();
        $form->attributes = $this->requestData;
        return $form->save();
    }

    /**
     * 账户提现
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionWithdraw(){
        $form = new MchManaAccountWithdrawForm();
        $form->attributes = $this->requestData;
        return $form->save();
    }

    /**
     * 提现记录
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionWithdrawLog(){
        $form = new MchManaAccountWithdrawLogForm();
        $form->attributes = $this->requestData;
        return $form->getList();
    }

    /**
     * 收益信息
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionIncomeLog(){
        $form = new MchManaAccountIncomeLogForm();
        $form->attributes = $this->requestData;
        return $form->getList();
    }

    /**
     * 获取账户信息
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionInfo(){
        $form = new MchManaAccountInfoForm();
        $form->attributes = $this->requestData;
        return $form->get();
    }

    /**
     * 账户明细列表
     * @return array
     * @throws \Exception
     */
    public function actionLog(){
        $form = new MchManaAccountLogForm();
        $form->attributes = $this->requestData;
        return $form->getList();
    }
}