<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-29
 * Time: 16:52
 */

namespace app\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\forms\api\cash\CashForm;

class CashController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => ['config']
            ],
        ]);
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-29
     * @Time: 19:40
     * @Note:提现相关设置
     */

    public function actionCashSetting()
    {

        $form = new CashForm();
        $form->attributes = $this->requestData;
        return $form->getSetting();
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-29
     * @Time: 16:54
     * @Note:提现申请提交
     */
    public function actionCashSubmit()
    {
        $form = new CashForm();
        $form->attributes = $this->requestData;
        return $form->save();
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-29
     * @Time: 16:55
     * @Note:提现记录
     */
    public function actionCashLog()
    {
        $form = new CashForm();
        $form->attributes = $this->requestData;
        return $form->getCashLogList();
    }


    public function actionList(){

        $form = new CashForm();
        $form->attributes = $this->requestData;
        return $form->getCashList();
    }


    public function actionDetail(){

        $form = new CashForm();
        $form->attributes = $this->requestData;
        return $form->getCashDetail();
    }


}