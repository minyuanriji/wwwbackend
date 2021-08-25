<?php

namespace app\plugins\area\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\events\CommonOrderDetailEvent;
use app\handlers\CommonOrderDetailHandler;
use app\models\TestModel;
use app\plugins\ApiController;
use app\plugins\area\forms\api\AreaApplyForm;
use app\plugins\area\forms\api\AreaForm;
use app\plugins\area\forms\api\TeamListForm;
use app\plugins\area\models\AreaApply;

class AreaController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function actionApply()
    {
        $form = new AreaApplyForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->apply());
    }

    /**
     * @Note:分销中心
     */
    public function actionInfo()
    {
        $form = new AreaForm();
        return $this->asJson($form->getInfo());
    }


    /**
     * @Note:分销日志
     * @return \yii\web\Response
     */
    public function actionLogList()
    {
        $form = new AreaForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getLogList());
    }

    /**
     * @Note:获取团队列表
     */
    public function actionTeamList()
    {
        $form = new TeamListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

}