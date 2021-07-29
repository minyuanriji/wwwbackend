<?php

namespace app\plugins\giftpacks\controllers\api;


use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\giftpacks\forms\api\GiftpacksGroupJoinForm;
use app\plugins\giftpacks\forms\api\GiftpacksGroupNewForm;

class GroupController extends ApiController {

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ]
        ]);
    }

    /**
     * 新增一个拼单
     * @return \yii\web\Response
     */
    public function actionNewGroup(){
        $form = new GiftpacksGroupNewForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->addGroup());
    }

    /**
     * 参与拼单
     * @return \yii\web\Response
     */
    public function actionJoin(){
        $form = new GiftpacksGroupJoinForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->join());
    }

}