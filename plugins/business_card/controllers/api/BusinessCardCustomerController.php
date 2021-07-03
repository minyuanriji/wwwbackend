<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 名片
 * Author: zal
 * Date: 2020-06-29
 * Time: 16:51
 */

namespace app\plugins\business_card\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\plugins\ApiController;
use app\plugins\business_card\BaseController;
use app\plugins\business_card\forms\api\BusinessCardCustomerForm;
use app\plugins\business_card\forms\api\BusinessCardCustomerLogForm;
use app\plugins\business_card\forms\api\BusinessCardCustomerTeamForm;
use app\plugins\business_card\forms\api\BusinessCardTrackStatForm;

class BusinessCardCustomerController extends BaseController
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
     * 添加商机
     * @return \yii\web\Response
     */
    public function actionBusiness(){
        $businessCardCustomerForm = new BusinessCardCustomerForm();
        $businessCardCustomerForm->attributes = $this->requestData;
        $result = $businessCardCustomerForm->add();
        return $this->asJson($result);
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-07-10
     * @Time: 16:58
     * @Note: 跟进记录
     */
    public function actionFollowList()
    {
        $form = new BusinessCardCustomerLogForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

    /**
     * 客户详情
     * @return \yii\web\Response
     */
    public function actionDetail(){
        $form = new BusinessCardCustomerForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->detail());
    }

    /**
     * 客户基本信息设置
     * @return \yii\web\Response
     */
    public function actionSetting(){
        $form = new BusinessCardCustomerForm();
        $form->basicInfo = $this->requestData["basic_info"];
        $form->id = $this->requestData["id"];
        return $this->asJson($form->setting());
    }

    /**
     * 添加跟进记录
     * @return \yii\web\Response
     */
    public function actionFollow()
    {
        $businessCardCustomerForm = new BusinessCardCustomerForm();
        $businessCardCustomerForm->attributes = $this->requestData;
        $result = $businessCardCustomerForm->follow();
        return $this->asJson($result);
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-07-15
     * @Time: 16:58
     * @Note: 浏览记录
     */
    public function actionBrowsingHistory()
    {
        $form = new BusinessCardTrackStatForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList(2));
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-07-15
     * @Time: 16:58
     * @Note: AI分析
     */
    public function actionAiAnalysis()
    {
        $form = new BusinessCardCustomerForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->aiAnalysis());
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-07-15
     * @Time: 16:58
     * @Note: 团队列表
     */
    public function actionTeamList()
    {
        $form = new BusinessCardCustomerTeamForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->teamList());
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-07-15
     * @Time: 16:58
     * @Note: 我的客户
     */
    public function actionMyClient()
    {
        $form = new BusinessCardCustomerTeamForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->myClient());
    }

}