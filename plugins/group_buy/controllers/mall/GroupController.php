<?php
/**
 * xuyaoxiang
 * 2020/09/04
 */

namespace app\plugins\group_buy\controllers\mall;

use app\plugins\Controller;
use app\plugins\group_buy\forms\mall\ActiveQueryForm;

class GroupController extends Controller
{
    //拼团列表
    public function actionList()
    {
        return $this->render('list');
    }

    //拼团详情
    public function actionDetail()
    {
        return $this->render('detail');
    }

    //拼团列表接口
    public function actionGetList()
    {
        $params           = \Yii::$app->request->get();
        $form             = new ActiveQueryForm();
        $form->attributes = $params;
        $return           = $form->queryList();

        return $this->asJson($return);
    }

    //拼团详情接口
    public function actionShow()
    {
        $params   = \Yii::$app->request->get();
        $form     = new ActiveQueryForm();
        $form->id = $params['id'];
        $return   = $form->show();

        return $this->asJson($return);
    }

    //结束拼团
    public function actionManualEnd(){
        $params   = \Yii::$app->request->get();
        $form     = new ActiveQueryForm();
        $form->attributes = $params;
        $return   = $form->manualEnd();

        return $this->asJson($return);
    }
}