<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 名片-行为轨迹
 * Author: zal
 * Date: 2020-07-10
 * Time: 16:51
 */

namespace app\plugins\business_card\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\plugins\ApiController;
use app\plugins\business_card\BaseController;
use app\plugins\business_card\forms\api\BusinessCardTrackLogForm;
use app\plugins\business_card\forms\api\BusinessCardTrackStatForm;

class TrackLogController extends BaseController
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
     * @Date: 2020-07-10
     * @Time: 16:58
     * @Note: 行为
     */
    public function actionBehavior()
    {
        $form = new BusinessCardTrackStatForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-07-09
     * @Time: 17:42
     * @Note: 轨迹
     * @return \yii\web\Response
     */
    public function actionTrack()
    {
        $form = new BusinessCardTrackLogForm();
        $form->time_type = isset($this->requestData["time_type"]) ? $this->requestData["time_type"] : -1;
        return $this->asJson($form->trackStat());
    }

    /**
     * 添加行为轨迹
     * @return \yii\web\Response
     */
    public function actionAddTrack(){
        $form = new BusinessCardTrackLogForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->addTrack());
    }

}