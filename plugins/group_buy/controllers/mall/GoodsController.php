<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-10
 * Time: 20:19
 */

namespace app\plugins\group_buy\controllers\mall;

use app\plugins\group_buy\forms\mall\GoodsListForm;
use app\controllers\mall\MallController;

class GoodsController extends MallController
{

    /**
     * @Author: 广东七件事 xuyaoxiang
     * @Date: 2020-09-28
     * @Time: 9:58
     * @Note:商品列表
     * @return bool|string|\yii\web\Response
     */
    public function actionIndex()
    {
        $form = new GoodsListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->get('search');
        $res = $form->getList();
        return $this->asJson($res);
    }
}