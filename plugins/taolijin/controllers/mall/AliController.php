<?php

namespace app\plugins\taolijin\controllers\mall;

use app\plugins\Controller;
use app\plugins\taolijin\forms\mall\TaoLiJinAliChangeOpenForm;
use app\plugins\taolijin\forms\mall\TaoLiJinAliDeleteForm;
use app\plugins\taolijin\forms\mall\TaoLiJinAliDelInviteCodeForm;
use app\plugins\taolijin\forms\mall\TaoLiJinAliInviteCodeListForm;
use app\plugins\taolijin\forms\mall\TaoLiJinAliListForm;
use app\plugins\taolijin\forms\mall\TaoLiJinAliNewInviteCodeForm;
use app\plugins\taolijin\forms\mall\TaoLiJinAliSearchForm;
use app\plugins\taolijin\forms\mall\TaoLiJinAliEditForm;

class AliController extends Controller{

    /**
     * 联盟列表
     * @return string|\yii\web\Response
     */
    public function actionList(){
        if (\Yii::$app->request->isAjax) {
            $form = new TaoLiJinAliListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('setting');
        }
    }

    /**
     * 编辑账号
     * @return string|\yii\web\Response
     */
    public function actionEdit(){
        $form = new TaoLiJinAliEditForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 切换是否启用状态
     * @return string|\yii\web\Response
     */
    public function actionChangeOpen(){
        $form = new TaoLiJinAliChangeOpenForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 切换是否启用状态
     * @return string|\yii\web\Response
     */
    public function actionDelete(){
        $form = new TaoLiJinAliDeleteForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }

    /**
     * 阿里联盟商品搜索
     * @return string|\yii\web\Response
     */
    public function actionSearch(){
        $form = new TaoLiJinAliSearchForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    /**
     * 邀请码列表
     * @return string|\yii\web\Response
     */
    public function actionInviteCodeList(){
        $form = new TaoLiJinAliInviteCodeListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    /**
     * 新增邀请码
     * @return string|\yii\web\Response
     */
    public function actionNewInviteCode(){
        $form = new TaoLiJinAliNewInviteCodeForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 新增邀请码
     * @return string|\yii\web\Response
     */
    public function actionDeleteInviteCode(){
        $form = new TaoLiJinAliDelInviteCodeForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }
}