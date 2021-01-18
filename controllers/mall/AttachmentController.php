<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 15:50
 */

namespace app\controllers\mall;


use app\core\ApiCode;
use app\forms\common\attachment\CommonAttachment;

class AttachmentController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {

        } else {
            return $this->render('index');
        }
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 16:04
     * @Note:上传设置
     * @return string|\yii\web\Response
     * @throws \Exception
     */
    public function actionAttachment()
    {
        if (\Yii::$app->request->isAjax) {
            $user = \Yii::$app->admin->identity;
            $common = CommonAttachment::getCommon($user, \Yii::$app->mall);
            $list = $common->getAttachmentList();
            $attachment = $common->getAttachment();
            $storage = $common->getStorage();
            return $this->asJson([
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'storageTypes' => $common->getStorageType(),
                    'storage' => $attachment ? $storage[$attachment->type] : '暂无配置',
                    'nickname' => $common->user->username
                ]
            ]);
        } else {
            return $this->render('attachment');
        }
    }

}