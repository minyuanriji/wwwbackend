<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-13
 * Time: 9:28
 */

namespace app\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\forms\api\goods_footmark\GoodsFootmarkForm;



class GoodsFootmarkController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function actionList()
    {
        $form = new GoodsFootmarkForm();
        $form->attributes = $this->requestData;
        $this->asJson($form->getList());
    }
    public function actionDelete()
    {
        $form = new GoodsFootmarkForm();
        $form->attributes = $this->requestData;
        $this->asJson($form->delete());
    }

}