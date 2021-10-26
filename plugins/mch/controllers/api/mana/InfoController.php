<?php
namespace app\plugins\mch\controllers\api\mana;

use app\forms\common\attachment\CommonAttachment;
use app\plugins\mch\forms\api\mana\MchManaInfoBaseForm;
use app\plugins\mch\forms\api\mana\MchManaInfoPosterForm;
use app\plugins\mch\forms\api\mana\MchManaInfoSetPicForm;
use app\plugins\mch\forms\api\mana\MchManaAccountSetWithdrawPwdForm;
use app\plugins\mch\forms\api\mana\MchManaInfoUpdateForm;

class InfoController extends MchAdminController {

    /**
     * 获取基本信息
     * @return \yii\web\Response
     */
    public function actionBase(){
        $form = new MchManaInfoBaseForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->get());
    }

    /**
     * 更新信息
     * @return \yii\web\Response
     */
    public function actionUpdate(){
        $form = new MchManaInfoUpdateForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->save());
    }

    /**
     * 分享海报
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionPoster(){
        $shareForm = new MchManaInfoPosterForm();
        $shareForm->attributes = $this->requestData;
        return $shareForm->getSharePoster();
    }

    /**
     * 上传图片
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionUpload(){
        $admin_id = 1;//前端调用借口走admin账号1
        $from = 2; //来源1后台2前台
        $result = CommonAttachment::addAttachmentInfo($from , static::$adminUser['mall_id'], $admin_id);
        return $result;
    }

    /**
     * 设置图片
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionSetPics(){
        $form = new MchManaInfoSetPicForm();
        $form->attributes = $this->requestData;
        $this->asJson($form->save());
    }

}