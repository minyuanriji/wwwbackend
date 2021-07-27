<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-28
 * Time: 9:31
 */

namespace app\plugins\mpwx\controllers\api;

class LoginController extends Controller
{

    public function actionIndex()
    {
        $res = $this->app->auth->session(\Yii::$app->request->get('code'));   //获取openid
        return $this->asJson(['code' => 0, 'msg' => $res]);
    }

}