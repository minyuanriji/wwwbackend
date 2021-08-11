<?php
namespace app\plugins\mch\controllers\mall;

use app\forms\common\AttachmentUploadForm;
use app\plugins\Controller;
use app\plugins\mch\forms\mall\MchAppsDeleteForm;
use app\plugins\mch\forms\mall\MchAppsDetailForm;
use app\plugins\mch\forms\mall\MchAppsEditForm;
use app\plugins\mch\forms\mall\MchAppsListForm;
use yii\web\UploadedFile;

class AppsController extends Controller{

    public function actionIndex(){
        if (\Yii::$app->request->isAjax) {
            $form = new MchAppsListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    /**
     * 编辑
     * @return string|yii\web\Response
     */
    public function actionEdit(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $data = \Yii::$app->request->post('form');
                $form = new MchAppsEditForm();
                $form->attributes = $data;
                $form->id         = \Yii::$app->request->get('id');
                $form->platform   = \Yii::$app->request->get('platform');
                return $this->asJson($form->save());
            } else {
                $form = new MchAppsDetailForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }

    /**
     * 删除
     * @return string|yii\web\Response
     */
    public function actionDestroy(){
        $form = new MchAppsDeleteForm();
        $form->attributes = \Yii::$app->request->post();
         return $this->asJson($form->delete());
    }

    /**
     * 上传APP文件
     * @return string|yii\web\Response
     */
    public function actionUpload(){
        $form = new AttachmentUploadForm();
        $form->file     = UploadedFile::getInstanceByName('file');
        $form->from     = 1;
        $form->mall_id  = \Yii::$app->mall->id;
        $form->admin_id = \Yii::$app->admin->id;
        $form->group_id = 0;
        $form->customSaveName = "bsh_merchant.apk";
        $form->addSupportBinaryExt("apk");
        return $form->save();
    }
}