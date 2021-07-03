<?php
namespace app\plugins\commission\controllers\mall;


use app\core\ApiCode;
use app\plugins\Controller;
use Yii;

class SettingController extends Controller{

    public function actionOptions(){

        if (Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {

            }else{
                return $this->asJson([
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '',
                    'data' => [
                        'setting' => [
                            'invited_price_type' => 2,
                            'commissionLevel'    => [
                            ],
                        ]
                    ]
                ]);
            }
        }

        return $this->render('options');
    }
}