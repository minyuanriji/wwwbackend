<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 用户接口类
 * Author: zal
 * Date: 2020-04-24
 * Time: 12:01
 */

namespace app\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\forms\api\identity\ForgetPasswordForm;
use app\forms\api\identity\SmsForm;
use app\forms\api\user\UserAddressForm;
use app\forms\api\user\UserEditForm;
use app\forms\api\user\UserForm;
use app\forms\api\user\UserIncomeForm;
use app\forms\common\attachment\CommonAttachment;

class IncomeController extends ApiController
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
     * 获取用户收益信息
     * @Author: zal
     * @Date: 2020-05-07
     * @Time: 14:33
     * @return array
     */
    public function actionInfo()
    {
        $form = new UserIncomeForm();
        return $form->getIncomeInfo();
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-01
     * @Time: 16:49
     * @Note:收益记录
     * @return array
     */
    public function actionList(){
        $form = new UserIncomeForm();
        $form->attributes = $this->requestData;
        return $form->getList();
    }
}
