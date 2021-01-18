<?php
/**
 * Created by PhpStorm.
 * User: 阿源
 * Date: 2020/11/3
 * Time: 9:45
 */

namespace app\controllers\api;



use app\forms\api\ExternalCallForm;

class ExternalCallController extends ApiController
{


    /**
     * 获取公告数据外部调用
     * @return \yii\web\Response
     */
    public function actionGetNotice(){
        $form = new ExternalCallForm();

        return $this->asJson($form->getNotice());
    }

    /**
     * 获取论坛数据给外部调用
     * @return \yii\web\Response
     */
    public function actionGetForum(){
        $form = new ExternalCallForm();

        return $this->asJson($form->getForum());
    }

    /**
     * 获取客服联系电话地址等数据给外部调用
     * @return \yii\web\Response
     */
    public function actionGetServices(){
        $form = new ExternalCallForm();

        return $this->asJson($form->getService());
    }


}