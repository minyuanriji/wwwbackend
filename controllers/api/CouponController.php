<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-05
 * Time: 9:36
 */

namespace app\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\forms\api\coupon\CouponDetailForm;
use app\forms\api\coupon\CouponListForm;
use app\forms\api\coupon\CouponReceiveForm;
use app\forms\api\coupon\UserCouponListForm;

/**
 * Class CouponController
 * @package app\controllers\api
 * @Notes 优惠券接口
 */
class CouponController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => ['list', 'detail']
            ],
        ]);
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-05
     * @Time: 10:23
     * @Note:优惠券详情
     * @return array
     *
     */
    public function actionDetail()
    {
        $form = new CouponDetailForm();
        $form->attributes = $this->requestData;
        return $form->search();
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-05
     * @Time: 10:23
     * @Note:领券优惠券
     * @return array
     */
    public function actionReceive()
    {
        $form = new CouponReceiveForm();
        $form->attributes = $this->requestData;
        return $form->receive();
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-06
     * @Time: 17:59
     * @Note:获取优惠券列表
     * @return array
     */
    public function actionList()
    {
        $form = new CouponListForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->getList();
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-06-01
     * @Time: 16:42
     * @Note:用户自己的优惠券
     * @return array
     */
    public function actionUserCouponList()
    {
        $form = new UserCouponListForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->getList();
    }

}