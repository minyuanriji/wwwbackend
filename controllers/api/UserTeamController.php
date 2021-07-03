<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 用户团队
 * Author: zal
 * Date: 2020-06-22
 * Time: 12:01
 */

namespace app\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\forms\api\user\UserTeamForm;
use app\logic\UserLogic;
use app\models\MemberLevel;
use app\models\User;

class UserTeamController extends ApiController
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
     * 我的团队数据
     * @return array
     */
    public function actionInfo(){
        $userTeamForm = new UserTeamForm();
        return $userTeamForm->getMyTeamData();
    }

    /**
     * 我的团队成员列表
     * @return array
     */
    public function actionList(){
        $userTeamForm = new UserTeamForm();
        $userTeamForm->attributes = $this->requestData;
        return $userTeamForm->getTeamList();
    }

    /**
     * 我的团队成员订单分佣列表
     * @return array
     */
    public function actionOrder(){
        $userTeamForm = new UserTeamForm();
        $userTeamForm->attributes = $this->requestData;
        return $userTeamForm->getTeamOrderList();
    }
}