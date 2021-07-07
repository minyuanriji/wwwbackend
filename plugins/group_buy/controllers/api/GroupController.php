<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/9/10
 * Time: 9:13
 */

namespace app\plugins\group_buy\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\plugins\ApiController;
use app\plugins\group_buy\forms\api\ActiveQueryForm;

class GroupController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => ['list','show']
            ],
        ]);
    }

    /**
     * 开团列表
     * @return \yii\web\Response
     */
    public function actionList()
    {
        $form             = new ActiveQueryForm();
        $form->attributes = $this->requestData;
        $return           = $form->queryList();
        return $this->asJson($return);
    }

    /**
     * 开团详情
     * @return \yii\web\Response
     */
    public function actionShow()
    {
        $form             = new ActiveQueryForm();
        $form->attributes = $this->requestData;
        $return           = $form->show();
        return $this->asJson($return);
    }
}