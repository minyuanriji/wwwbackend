<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 拼单列表
 * Author: xuyaoxiang
 * Date: 2020/9/7
 * Time: 14:08
 */

namespace app\plugins\group_buy\controllers\mall;

use app\plugins\Controller;
use app\plugins\group_buy\forms\mall\ActiveItemQueryForm;

class ActiveController extends Controller
{
    //拼单列表页面
    public function actionList()
    {
        return $this->render('list');
    }

    //拼单列表接口
    public function actionGetList()
    {
        $params           = \Yii::$app->request->get();
        $form             = new ActiveItemQueryForm();
        $form->attributes = $params;
        $return           = $form->queryList();

        return $this->asJson($return);
    }
}