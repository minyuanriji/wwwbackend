<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * boss雷达
 * Author: zal
 * Date: 2020-07-20
 * Time: 18:51
 */

namespace app\plugins\business_card\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\plugins\ApiController;
use app\plugins\business_card\BaseController;
use app\plugins\business_card\forms\api\BusinessCardCustomerLogForm;
use app\plugins\business_card\forms\api\MessageCenterForm;

class MessageCenterController extends BaseController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-06-29
     * @Time: 16:58
     * @Note: 消息中心-新增消息
     */
    public function actionAdd()
    {
        $form = new MessageCenterForm();
        $form->attributes = $this->requestData;
        $form->form_data = isset($this->requestData["form_data"]) ? $this->requestData["form_data"] : [];
        return $this->asJson($form->addMessage());
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-06-29
     * @Time: 16:58
     * @Note: 消息中心-所有会话列表
     */
    public function actionList()
    {
        $form = new MessageCenterForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getMessage());
    }

    /**
     * 客服服务
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\DecryptException
     */
    public function actionCustomerService(){
        $form = new BusinessCardCustomerLogForm();
        $form->attributes = $this->requestData;
        return $form->addNewClue();
    }

}