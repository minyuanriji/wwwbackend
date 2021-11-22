<?php
namespace app\plugins\bsh_app\controllers\mall;

use app\forms\common\AttachmentUploadForm;
use app\plugins\bsh_app\forms\mall\AppsDeleteForm;
use app\plugins\bsh_app\forms\mall\AppsDetailForm;
use app\plugins\bsh_app\forms\mall\AppsEditForm;
use app\plugins\bsh_app\forms\mall\AppsListForm;
use app\plugins\Controller;
use yii\web\UploadedFile;

class VersionController extends Controller {

    public function actionIndex(){
        if (\Yii::$app->request->isAjax) {
            $form = new AppsListForm();
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
                $form = new AppsEditForm();
                $form->attributes = $data;
                $form->id         = \Yii::$app->request->get('id');
                $form->platform   = \Yii::$app->request->get('platform');
                return $this->asJson($form->save());
            } else {
                $form = new AppsDetailForm();
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
        $form = new AppsDeleteForm();
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
        $form->customSaveName = "bsh.apk";
        $form->addSupportBinaryExt("apk");
        return $form->save();
    }
}