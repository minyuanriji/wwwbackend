<?php
namespace app\mch\forms\common\attachment;

use app\core\ApiCode;
use app\mch\forms\common\AttachmentUploadForm;
use app\models\BaseModel;
use yii\web\UploadedFile;

class CommonAttachment extends BaseModel{

    /**
     * 上传图片公共方法
     * @param $from
     * @param $mall_id
     * @param $admin_id
     * @param $group_id
     * @return array
     */
    public static function addAttachmentInfo($from, $mall_id, $admin_id, $group_id = 0){
        $form = new AttachmentUploadForm();

        if(empty(UploadedFile::getInstanceByName('file'))){
            return[
                'code' => ApiCode::CODE_FAIL,
                'data' => '请上传图片'
            ];
        }

        $form->file     = UploadedFile::getInstanceByName('file');
        $form->from     = $from;
        $form->mall_id  = $mall_id;
        $form->admin_id = $admin_id;
        $form->mch_id   = \Yii::$app->mchAdmin->identity->mchModel->id;
        $form->group_id = $group_id ? $group_id : 0;

        return $form->save();
    }

}