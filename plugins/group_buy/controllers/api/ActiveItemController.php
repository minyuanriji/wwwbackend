<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/9/11
 * Time: 15:44
 */

namespace app\plugins\group_buy\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\plugins\ApiController;
use app\plugins\group_buy\forms\common\ActiveItemQueryCommonForm;

class ActiveItemController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                //'ignore' => []
            ],
        ]);
    }

    /**
     * 根据订单编号获取拼团详情
     * @return \yii\web\Response
     */
    public function actionDetailByOrder()
    {
        $form             = new ActiveItemQueryCommonForm();
        $form->attributes = $this->requestData;
        $return           = $form->returnJoinOrderOne();
        return $this->asJson($return);
    }
}