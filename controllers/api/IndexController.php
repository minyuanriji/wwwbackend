<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-27
 * Time: 14:13
 */

namespace app\controllers\api;

use app\forms\api\IndexForm;
use app\forms\mall\data_statistics\TimingStatisticsForm;
use app\models\User;
use app\models\UserInfo;
use function EasyWeChat\Kernel\Support\get_client_ip;

class IndexController extends ApiController
{
    public function actionIndex()
    {
        $form = new IndexForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getIndex());
    }


    //时间段调用
    public function actionUpdateHour(){
        return ['msg'=>'注册成功','access_token' => "",'mobile'=>""];
    }
}