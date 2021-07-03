<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-06-29
 * Time: 16:51
 */

namespace app\plugins\boss\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\core\ApiCode;
use app\events\CommonOrderDetailEvent;
use app\handlers\CommonOrderDetailHandler;
use app\models\TestModel;
use app\plugins\ApiController;
use app\plugins\boss\forms\api\BossForm;
use app\plugins\boss\forms\api\TeamListForm;
use app\plugins\boss\models\BossLevel;

class BossController extends ApiController
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
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-06-29
     * @Time: 16:58
     * @Note:分销中心
     */
    public function actionInfo()
    {
        $form = new BossForm();
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
        $form = new BossForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getLogList());
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-25
     * @Time: 17:17
     * @Note:获取结算记录
     * @return \yii\web\Response
     */
    public function actionPriceList()
    {
        $form = new BossForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getPriceList());
    }

    public function actionLevelList()
    {
        $level_list = BossLevel::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'is_enable' => 1])->select('detail,name,level')->asArray()->all();
        return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '', 'data' => ['level_list' => $level_list]]);
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-08-03
     * @Time: 14:12
     * @Note:获取团队列表
     */
    public function actionTeamList()
    {
        $form = new TeamListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

}