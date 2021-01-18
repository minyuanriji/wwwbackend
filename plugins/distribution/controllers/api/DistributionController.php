<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-06-29
 * Time: 16:51
 */

namespace app\plugins\distribution\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\events\CommonOrderDetailEvent;
use app\handlers\CommonOrderDetailHandler;
use app\models\TestModel;
use app\plugins\ApiController;
use app\plugins\distribution\forms\api\DistributionApplyForm;
use app\plugins\distribution\forms\api\DistributionForm;

class DistributionController extends ApiController
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
     * @Date: 2020-09-10
     * @Time: 14:58
     * @Note:分销中心
     */
    public function actionInfo()
    {
        $form = new DistributionForm();
        return $this->asJson($form->getInfo());
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-06-29
     * @Time: 16:58
     * @Note:分销中心
     */
    public function actionRebuyInfo()
    {
        $form = new DistributionForm();
        return $this->asJson($form->getInfo());
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-06-29
     * @Time: 17:42
     * @Note:分销日志
     * @return \yii\web\Response
     */
    public function actionLogList()
    {
        $form = new DistributionForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getLogList());
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-06-29
     * @Time: 17:42
     * @Note:复购奖励
     * @return \yii\web\Response
     */
    public function actionRebuyPriceList()
    {
        $form = new DistributionForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getRebuyPriceList());
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-08-04
     * @Time: 16:46
     * @Note:团队奖励
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionTeamPriceList()
    {
        $form = new DistributionForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getTeamPriceList());
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-06-29
     * @Time: 17:42
     * @Note:补贴
     * @return \yii\web\Response
     */
    public function actionSubsidyPriceList()
    {
        $form = new DistributionForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getSubsidyPriceList());
    }

    public function actionApply()
    {
        $form = new DistributionApplyForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->apply());
    }


}