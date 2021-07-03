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
use app\plugins\business_card\forms\api\RadarForm;

class RadarController extends BaseController
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
     * @Note: 雷达-概括
     */
    public function actionGeneral()
    {
        $form = new RadarForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->general());
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-06-29
     * @Time: 16:58
     * @Note: 概括-商城订单统计
     */
    public function actionMallOrderStat()
    {
        $form = new RadarForm();
        $form->page_type = 1;
        $form->attributes = $this->requestData;
        return $this->asJson($form->generalStat());
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-06-29
     * @Time: 16:58
     * @Note: 概括-新增客户统计
     */
    public function actionAddCustomerStat()
    {
        $form = new RadarForm();
        $form->page_type = 2;
        $form->attributes = $this->requestData;
        return $this->asJson($form->generalStat());
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-06-29
     * @Time: 16:58
     * @Note: 概括-咨询客户统计
     */
    public function actionAdvisoryCustomerStat()
    {
        $form = new RadarForm();
        $form->page_type = 3;
        $form->attributes = $this->requestData;
        return $this->asJson($form->generalStat());
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-06-29
     * @Time: 16:58
     * @Note: 概括-跟进客户统计
     */
    public function actionFollowCustomerStat()
    {
        $form = new RadarForm();
        $form->page_type = 4;
        $form->attributes = $this->requestData;
        return $this->asJson($form->generalStat());
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-06-29
     * @Time: 16:58
     * @Note: 概括-客户兴趣统计
     */
    public function actionInterestStat()
    {
        $form = new RadarForm();
        $form->page_type = 5;
        $form->attributes = $this->requestData;
        return $this->asJson($form->generalStat());
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-06-29
     * @Time: 16:58
     * @Note: 概括-用户活跃度统计
     */
    public function actionUserActivityStat()
    {
        $form = new RadarForm();
        $form->page_type = 6;
        $form->attributes = $this->requestData;
        return $this->asJson($form->generalStat());
    }

    /**
     * 销售排行
     * @return \yii\web\Response
     */
    public function actionSalesRanking(){
        $form = new RadarForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->salesRanking());
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-07-23
     * @Time: 15:58
     * @Note: 热度排行
     */
    public function actionHotRanking()
    {
        $form = new RadarForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->hotRanking());
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-07-15
     * @Time: 16:58
     * @Note: AI分析
     */
    public function actionAiAnalysis()
    {
        $form = new RadarForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->aiAnalysis());
    }

}