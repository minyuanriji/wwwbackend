<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-10
 * Time: 16:36
 */

namespace app\controllers;

use app\controllers\behavior\LoginFilter;

class KeepActiveController extends BaseController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'loginFilter' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function actionIndex()
    {
    }
}
